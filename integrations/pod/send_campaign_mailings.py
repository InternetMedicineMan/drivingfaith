import argparse
import html
import re
import secrets
from datetime import date, timedelta
from urllib.parse import urlencode

from lob_python.exceptions import ApiException

from db import get_connection
from pod.lob_letters import build_campaign_merge_variables, send_campaign_mailing
from settings import LOB_PUBLIC_DOMAIN, POD_BATCH_LIMIT


REQUIRED_FIELDS = ("first_name", "last_name", "address1", "city", "state", "zip")
TEMPLATE_VARIABLE_PATTERN = re.compile(r"{{\s*([a-zA-Z0-9_.]+)\s*}}")


def fetch_due_mailings(conn, limit, campaign_id=None):
    query = """
        SELECT
            enrollments.id AS enrollment_id,
            enrollments.team_id,
            enrollments.campaign_id,
            contacts.id AS contact_id,
            contacts.first_name,
            contacts.last_name,
            contacts.organization,
            contacts.email,
            contacts.phone,
            contacts.address1,
            contacts.address2,
            contacts.city,
            contacts.state,
            contacts.zip,
            contacts.country,
            campaigns.name AS campaign_name,
            planned.id AS enrollment_mailing_id,
            planned.sequence,
            planned.scheduled_for,
            planned.override_cover_letter_html,
            planned.rendered_html,
            planned.render_token,
            mailings.id AS mailing_id,
            mailings.name AS mailing_name,
            mailings.delay_days_after_previous,
            mailings.pause_until_reply,
            mailings.provider,
            mailings.provider_template_id,
            mailings.mail_class,
            mailings.color,
            mailings.double_sided,
            mailings.address_placement,
            mailings.return_envelope,
            mailings.perforated_page,
            cover_templates.html_content AS cover_letter_html,
            override_cover_templates.html_content AS override_cover_letter_template_html
        FROM pod_enrollment_mailings planned
        INNER JOIN pod_campaign_enrollments enrollments
            ON enrollments.id = planned.campaign_enrollment_id
        INNER JOIN ministry_contacts contacts
            ON contacts.id = planned.contact_id
        INNER JOIN pod_campaigns campaigns
            ON campaigns.id = enrollments.campaign_id
        INNER JOIN pod_campaign_mailings mailings
            ON mailings.id = planned.campaign_mailing_id
        LEFT JOIN pod_content_templates cover_templates
            ON cover_templates.id = planned.cover_letter_template_id
        LEFT JOIN pod_content_templates override_cover_templates
            ON override_cover_templates.id = planned.override_cover_letter_template_id
        WHERE enrollments.status = 'active'
            AND campaigns.status = 'active'
            AND mailings.status = 'active'
            AND planned.status IN ('planned', 'failed')
            AND (enrollments.paused_until IS NULL OR enrollments.paused_until <= CURRENT_DATE)
            AND planned.scheduled_for <= CURRENT_DATE
    """
    params = []

    if campaign_id is not None:
        query += " AND enrollments.campaign_id = %s"
        params.append(campaign_id)

    query += " ORDER BY planned.scheduled_for IS NULL DESC, planned.scheduled_for, planned.id"

    if limit is not None:
        query += " LIMIT %s"
        params.append(limit)

    cursor = conn.cursor(dictionary=True)
    cursor.execute(query, tuple(params))
    return cursor.fetchall()


def fetch_enrollment(conn, enrollment_id):
    cursor = conn.cursor(dictionary=True)
    cursor.execute(
        """
        SELECT *
        FROM pod_campaign_enrollments
        WHERE id = %s
        """,
        (enrollment_id,),
    )
    return cursor.fetchone()


def fetch_enrollments_for_campaign(conn, campaign_id):
    cursor = conn.cursor(dictionary=True)
    cursor.execute(
        """
        SELECT id
        FROM pod_campaign_enrollments
        WHERE campaign_id = %s
        ORDER BY id
        """,
        (campaign_id,),
    )
    return cursor.fetchall()


def fetch_pod_campaign_mailings(conn, campaign_id):
    cursor = conn.cursor(dictionary=True)
    cursor.execute(
        """
        SELECT *
        FROM pod_campaign_mailings
        WHERE campaign_id = %s
            AND status = 'active'
        ORDER BY sequence
        """,
        (campaign_id,),
    )
    return cursor.fetchall()


def fetch_next_planned_mailing(conn, enrollment_id, current_sequence):
    cursor = conn.cursor(dictionary=True)
    cursor.execute(
        """
        SELECT planned.id, planned.campaign_mailing_id, planned.sequence, mailings.delay_days_after_previous
        FROM pod_enrollment_mailings planned
        INNER JOIN pod_campaign_mailings mailings
            ON mailings.id = planned.campaign_mailing_id
        WHERE planned.campaign_enrollment_id = %s
            AND planned.sequence > %s
            AND planned.status = 'planned'
        ORDER BY planned.sequence
        LIMIT 1
        """,
        (enrollment_id, current_sequence),
    )
    return cursor.fetchone()


def fetch_mailing_pages(conn, mailing_id):
    cursor = conn.cursor(dictionary=True)
    cursor.execute(
        """
        SELECT page_number, name, html_content
        FROM pod_campaign_mailing_pages
        WHERE campaign_mailing_id = %s
        ORDER BY page_number
        """,
        (mailing_id,),
    )
    return cursor.fetchall()


def fetch_enrollment_for_reply(conn, enrollment_id):
    cursor = conn.cursor(dictionary=True)
    cursor.execute(
        """
        SELECT
            enrollments.id AS enrollment_id,
            enrollments.team_id,
            enrollments.contact_id,
            enrollments.next_mailing_id,
            enrollments.reply_required_by_mailing_id,
            next_planned.id AS next_enrollment_mailing_id,
            next_mailings.delay_days_after_previous,
            reply_planned.id AS reply_required_by_enrollment_mailing_id
        FROM pod_campaign_enrollments enrollments
        LEFT JOIN pod_enrollment_mailings next_planned
            ON next_planned.campaign_enrollment_id = enrollments.id
            AND next_planned.campaign_mailing_id = enrollments.next_mailing_id
        LEFT JOIN pod_campaign_mailings next_mailings
            ON next_mailings.id = enrollments.next_mailing_id
        LEFT JOIN pod_enrollment_mailings reply_planned
            ON reply_planned.campaign_enrollment_id = enrollments.id
            AND reply_planned.campaign_mailing_id = enrollments.reply_required_by_mailing_id
        WHERE enrollments.id = %s
        """,
        (enrollment_id,),
    )
    return cursor.fetchone()


def plan_enrollment(conn, enrollment_id):
    enrollment = fetch_enrollment(conn, enrollment_id)
    if enrollment is None:
        raise RuntimeError(f"Enrollment {enrollment_id} was not found.")

    mailings = fetch_pod_campaign_mailings(conn, enrollment["campaign_id"])
    cursor = conn.cursor()

    for mailing in mailings:
        scheduled_for = None
        if mailing["sequence"] == enrollment["current_sequence"]:
            scheduled_for = enrollment["next_send_on"] or date.today()

        cursor.execute(
            """
            INSERT IGNORE INTO pod_enrollment_mailings (
                team_id,
                campaign_enrollment_id,
                campaign_mailing_id,
                contact_id,
                sequence,
                status,
                scheduled_for,
                cover_letter_template_id,
                created_at,
                updated_at
            ) VALUES (%s, %s, %s, %s, %s, 'planned', %s, %s, NOW(), NOW())
            """,
            (
                enrollment["team_id"],
                enrollment["id"],
                mailing["id"],
                enrollment["contact_id"],
                mailing["sequence"],
                scheduled_for,
                mailing["cover_letter_template_id"],
            ),
        )

    conn.commit()
    return len(mailings)


def plan_campaign(conn, campaign_id):
    enrollments = fetch_enrollments_for_campaign(conn, campaign_id)
    planned_count = 0

    for enrollment in enrollments:
        planned_count += plan_enrollment(conn, enrollment["id"])

    return planned_count


def build_render_variables(contact):
    full_name = f"{contact.get('first_name') or ''} {contact.get('last_name') or ''}".strip()
    contact_values = {
        "first_name": contact.get("first_name") or "",
        "last_name": contact.get("last_name") or "",
        "full_name": full_name,
        "organization": contact.get("organization") or "",
        "email": contact.get("email") or "",
        "phone": contact.get("phone") or "",
        "address1": contact.get("address1") or "",
        "address2": contact.get("address2") or "",
        "city": contact.get("city") or "",
        "state": contact.get("state") or "",
        "zip": contact.get("zip") or "",
        "country": contact.get("country") or "US",
    }

    variables = {}
    for key, value in contact_values.items():
        escaped_value = html.escape(str(value), quote=True)
        variables[key] = escaped_value
        variables[f"contact.{key}"] = escaped_value

    return variables


def render_template(template, variables, template_label):
    if not template:
        return ""

    markers = set(TEMPLATE_VARIABLE_PATTERN.findall(template))
    unknown_markers = sorted(markers - set(variables.keys()))
    if unknown_markers:
        raise RuntimeError(
            f"{template_label} contains unsupported merge variable(s): "
            + ", ".join(f"{{{{ {marker} }}}}" for marker in unknown_markers)
        )

    return TEMPLATE_VARIABLE_PATTERN.sub(lambda match: variables[match.group(1)], template)


def resolve_rendered_html(conn, row, contact):
    if row.get("rendered_html"):
        return row["rendered_html"]

    cover_html = (
        row.get("override_cover_letter_html")
        or row.get("override_cover_letter_template_html")
        or row.get("cover_letter_html")
        or ""
    )
    pages = fetch_mailing_pages(conn, row["mailing_id"])

    if not cover_html and not pages:
        return None

    variables = build_render_variables(contact)
    rendered_parts = []

    if cover_html:
        rendered_parts.append(render_template(cover_html, variables, "Cover letter"))

    for page in pages:
        if page.get("html_content"):
            page_label = page.get("name") or f"Page {page['page_number']}"
            rendered_parts.append(render_template(page["html_content"], variables, page_label))

    return "\n".join(part for part in rendered_parts if part)


def save_rendered_html(conn, enrollment_mailing_id, rendered_html):
    cursor = conn.cursor()
    cursor.execute(
        """
        UPDATE pod_enrollment_mailings
        SET rendered_html = %s,
            rendered_at = NOW(),
            updated_at = NOW()
        WHERE id = %s
        """,
        (rendered_html, enrollment_mailing_id),
    )
    conn.commit()


def ensure_render_token(conn, enrollment_mailing_id):
    cursor = conn.cursor(dictionary=True)
    cursor.execute(
        """
        SELECT render_token
        FROM pod_enrollment_mailings
        WHERE id = %s
        """,
        (enrollment_mailing_id,),
    )
    row = cursor.fetchone()

    if row and row.get("render_token"):
        return row["render_token"]

    render_token = secrets.token_urlsafe(32)
    cursor.execute(
        """
        UPDATE pod_enrollment_mailings
        SET render_token = %s,
            updated_at = NOW()
        WHERE id = %s
        """,
        (render_token, enrollment_mailing_id),
    )
    conn.commit()

    return render_token


def build_render_url(enrollment_mailing_id, render_token):
    if not LOB_PUBLIC_DOMAIN:
        raise RuntimeError("Missing LOB_PUBLIC_DOMAIN. Lob needs a public URL for rendered campaign mailings.")

    query = urlencode({"token": render_token})
    return f"{LOB_PUBLIC_DOMAIN}/pod/render/enrollment-mailings/{enrollment_mailing_id}?{query}"


def ensure_delivery(conn, row):
    idempotency_key = (
        f"campaign-{row['campaign_id']}:"
        f"enrollment-{row['enrollment_id']}:"
        f"planned-{row['enrollment_mailing_id']}"
    )

    cursor = conn.cursor(dictionary=True)
    cursor.execute(
        """
        INSERT IGNORE INTO pod_deliveries (
            team_id,
            campaign_enrollment_id,
            enrollment_mailing_id,
            campaign_mailing_id,
            contact_id,
            status,
            scheduled_for,
            provider,
            idempotency_key,
            created_at,
            updated_at
        ) VALUES (%s, %s, %s, %s, %s, 'queued', %s, %s, %s, NOW(), NOW())
        """,
        (
            row["team_id"],
            row["enrollment_id"],
            row["enrollment_mailing_id"],
            row["mailing_id"],
            row["contact_id"],
            row["scheduled_for"] or date.today(),
            row["provider"],
            idempotency_key,
        ),
    )
    conn.commit()

    cursor.execute(
        """
        SELECT *
        FROM pod_deliveries
        WHERE idempotency_key = %s
        """,
        (idempotency_key,),
    )
    return cursor.fetchone()


def record_contact_event(
    conn,
    team_id,
    contact_id,
    event_type,
    source=None,
    source_label=None,
    summary=None,
    eventable_type=None,
    eventable_id=None,
):
    cursor = conn.cursor()
    cursor.execute(
        """
        INSERT INTO ministry_contact_events (
            team_id,
            contact_id,
            eventable_type,
            eventable_id,
            type,
            source,
            source_label,
            summary,
            occurred_at,
            created_at,
            updated_at
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW(), NOW())
        """,
        (
            team_id,
            contact_id,
            eventable_type,
            eventable_id,
            event_type,
            source,
            source_label,
            summary,
        ),
    )


def mark_delivery_sent(conn, row, delivery_id, provider_id):
    cursor = conn.cursor()
    cursor.execute(
        """
        UPDATE pod_deliveries
        SET status = 'sent',
            provider_id = %s,
            sent_at = NOW(),
            failed_at = NULL,
            error_message = NULL,
            attempt_count = attempt_count + 1,
            updated_at = NOW()
        WHERE id = %s
        """,
        (provider_id, delivery_id),
    )
    cursor.execute(
        """
        UPDATE pod_enrollment_mailings
        SET status = 'sent',
            sent_at = NOW(),
            updated_at = NOW()
        WHERE id = %s
        """,
        (row["enrollment_mailing_id"],),
    )
    record_contact_event(
        conn,
        row["team_id"],
        row["contact_id"],
        "pod_delivery",
        source="pod",
        source_label=row["mailing_name"],
        summary=f"Sent {row['campaign_name']} - {row['mailing_name']}.",
        eventable_type="App\\Models\\PodDelivery",
        eventable_id=delivery_id,
    )
    conn.commit()


def mark_delivery_failed(conn, row, delivery_id, error_message):
    cursor = conn.cursor()
    cursor.execute(
        """
        UPDATE pod_deliveries
        SET status = 'failed',
            failed_at = NOW(),
            error_message = %s,
            attempt_count = attempt_count + 1,
            updated_at = NOW()
        WHERE id = %s
        """,
        (str(error_message), delivery_id),
    )
    cursor.execute(
        """
        UPDATE pod_enrollment_mailings
        SET status = 'failed',
            updated_at = NOW()
        WHERE id = %s
        """,
        (row["enrollment_mailing_id"],),
    )
    conn.commit()


def advance_enrollment(conn, row):
    next_mailing = fetch_next_planned_mailing(conn, row["enrollment_id"], row["sequence"])
    cursor = conn.cursor()

    if row["pause_until_reply"]:
        cursor.execute(
            """
            UPDATE pod_campaign_enrollments
            SET status = 'waiting_for_reply',
                current_sequence = %s,
                next_mailing_id = %s,
                next_send_on = NULL,
                reply_required_by_mailing_id = %s,
                reply_required_at = NOW(),
                reply_received_at = NULL,
                updated_at = NOW()
            WHERE id = %s
            """,
            (
                next_mailing["sequence"] if next_mailing else row["sequence"],
                next_mailing["campaign_mailing_id"] if next_mailing else None,
                row["mailing_id"],
                row["enrollment_id"],
            ),
        )
    elif next_mailing is None:
        cursor.execute(
            """
            UPDATE pod_campaign_enrollments
            SET status = 'completed',
                completed_at = NOW(),
                next_mailing_id = NULL,
                next_send_on = NULL,
                updated_at = NOW()
            WHERE id = %s
            """,
            (row["enrollment_id"],),
        )
    else:
        next_send_on = date.today() + timedelta(days=next_mailing["delay_days_after_previous"])
        cursor.execute(
            """
            UPDATE pod_campaign_enrollments
            SET current_sequence = %s,
                next_mailing_id = %s,
                next_send_on = %s,
                updated_at = NOW()
            WHERE id = %s
            """,
            (
                next_mailing["sequence"],
                next_mailing["campaign_mailing_id"],
                next_send_on,
                row["enrollment_id"],
            ),
        )
        cursor.execute(
            """
            UPDATE pod_enrollment_mailings
            SET scheduled_for = %s,
                updated_at = NOW()
            WHERE id = %s
            """,
            (next_send_on, next_mailing["id"]),
        )

    conn.commit()


def record_reply(conn, enrollment_id, channel, summary, resume_on=None):
    enrollment = fetch_enrollment_for_reply(conn, enrollment_id)
    if enrollment is None:
        raise RuntimeError(f"Enrollment {enrollment_id} was not found.")

    next_send_on = resume_on
    if next_send_on is None and enrollment["next_mailing_id"] is not None:
        next_send_on = date.today() + timedelta(days=enrollment["delay_days_after_previous"])

    cursor = conn.cursor()
    cursor.execute(
        """
        INSERT INTO pod_replies (
            team_id,
            campaign_enrollment_id,
            enrollment_mailing_id,
            campaign_mailing_id,
            contact_id,
            channel,
            summary,
            created_at,
            updated_at
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
        """,
        (
            enrollment["team_id"],
            enrollment["enrollment_id"],
            enrollment["reply_required_by_enrollment_mailing_id"],
            enrollment["reply_required_by_mailing_id"],
            enrollment["contact_id"],
            channel,
            summary,
        ),
    )
    reply_id = cursor.lastrowid

    record_contact_event(
        conn,
        enrollment["team_id"],
        enrollment["contact_id"],
        "reply_received",
        source="pod",
        source_label=channel,
        summary=summary or "Reply received for POD campaign.",
        eventable_type="App\\Models\\PodReply",
        eventable_id=reply_id,
    )

    if enrollment["next_mailing_id"] is None:
        cursor.execute(
            """
            UPDATE pod_campaign_enrollments
            SET status = 'completed',
                completed_at = NOW(),
                reply_required_by_mailing_id = NULL,
                reply_required_at = NULL,
                reply_received_at = NOW(),
                updated_at = NOW()
            WHERE id = %s
            """,
            (enrollment_id,),
        )
    else:
        cursor.execute(
            """
            UPDATE pod_campaign_enrollments
            SET status = 'active',
                next_send_on = %s,
                reply_required_by_mailing_id = NULL,
                reply_required_at = NULL,
                reply_received_at = NOW(),
                updated_at = NOW()
            WHERE id = %s
            """,
            (next_send_on, enrollment_id),
        )
        cursor.execute(
            """
            UPDATE pod_enrollment_mailings
            SET scheduled_for = %s,
                updated_at = NOW()
            WHERE id = %s
            """,
            (next_send_on, enrollment["next_enrollment_mailing_id"]),
        )

    conn.commit()


def missing_required_fields(row):
    return [field for field in REQUIRED_FIELDS if not row.get(field)]


def as_contact(row):
    return {
        "id": row["contact_id"],
        "first_name": row["first_name"],
        "last_name": row["last_name"],
        "organization": row.get("organization"),
        "email": row.get("email"),
        "phone": row.get("phone"),
        "address1": row["address1"],
        "address2": row["address2"],
        "city": row["city"],
        "state": row["state"],
        "zip": row["zip"],
        "country": row.get("country") or "US",
    }


def as_campaign(row):
    return {
        "id": row["campaign_id"],
        "name": row["campaign_name"],
    }


def as_mailing(row, rendered_html=None):
    return {
        "id": row["mailing_id"],
        "name": row["mailing_name"],
        "sequence": row["sequence"],
        "pause_until_reply": row["pause_until_reply"],
        "provider": row["provider"],
        "provider_template_id": row["provider_template_id"],
        "rendered_html": rendered_html,
        "render_url": row.get("render_url"),
        "mail_class": row["mail_class"],
        "color": row["color"],
        "double_sided": row["double_sided"],
        "address_placement": row["address_placement"],
        "return_envelope": row["return_envelope"],
        "perforated_page": row["perforated_page"],
    }


def parse_args():
    parser = argparse.ArgumentParser(description="Send due campaign Bible study mailings.")
    batch_group = parser.add_mutually_exclusive_group()
    batch_group.add_argument(
        "--limit",
        type=int,
        default=POD_BATCH_LIMIT,
        help=f"Maximum number of due planned mailings to process. Defaults to {POD_BATCH_LIMIT}.",
    )
    batch_group.add_argument(
        "--all",
        action="store_true",
        help="Process all due planned mailings.",
    )
    parser.add_argument("--campaign-id", type=int, help="Only process planned mailings for one campaign.")
    parser.add_argument(
        "--plan-enrollment",
        type=int,
        metavar="ENROLLMENT_ID",
        help="Create planned mailing rows for one campaign enrollment.",
    )
    parser.add_argument(
        "--plan-campaign",
        type=int,
        metavar="CAMPAIGN_ID",
        help="Create planned mailing rows for every enrollment in one campaign.",
    )
    parser.add_argument(
        "--record-reply",
        type=int,
        metavar="ENROLLMENT_ID",
        help="Record a reply for an enrollment and resume or complete its sequence.",
    )
    parser.add_argument("--reply-channel", default="mail", help="Channel to store when recording a reply.")
    parser.add_argument("--reply-summary", help="Optional summary to store when recording a reply.")
    parser.add_argument(
        "--resume-on",
        help="Optional YYYY-MM-DD date to use as the resumed enrollment's next send date.",
    )
    parser.add_argument(
        "--send",
        action="store_true",
        help="Send real letters. Without this flag, the script runs as a dry run.",
    )
    parser.add_argument(
        "--prepare-render-urls",
        action="store_true",
        help="During a dry run, create missing render tokens so printed render URLs can be opened and tested.",
    )
    args = parser.parse_args()

    if args.limit is not None and args.limit < 1:
        parser.error("--limit must be 1 or greater")

    return args


def run():
    args = parse_args()
    limit = None if args.all else args.limit
    dry_run = not args.send
    should_prepare_render_urls = args.send or args.prepare_render_urls

    sent_count = 0
    skipped_count = 0
    failed_count = 0

    conn = get_connection()
    try:
        if args.plan_enrollment is not None:
            planned_count = plan_enrollment(conn, args.plan_enrollment)
            print(f"Ensured {planned_count} planned mailing(s) for enrollment {args.plan_enrollment}.")
            return

        if args.plan_campaign is not None:
            planned_count = plan_campaign(conn, args.plan_campaign)
            print(f"Ensured {planned_count} planned mailing(s) for campaign {args.plan_campaign}.")
            return

        if args.record_reply is not None:
            resume_on = date.fromisoformat(args.resume_on) if args.resume_on else None
            record_reply(conn, args.record_reply, args.reply_channel, args.reply_summary, resume_on)
            print(f"Recorded reply for enrollment {args.record_reply}.")
            return

        rows = fetch_due_mailings(conn, limit, args.campaign_id)
        limit_label = "all" if limit is None else str(limit)
        print(f"Found {len(rows)} due planned mailing(s) with limit={limit_label}.")

        for row in rows:
            contact = as_contact(row)
            campaign = as_campaign(row)
            missing = missing_required_fields(contact)

            if missing:
                skipped_count += 1
                print(f"Skipping planned mailing {row['enrollment_mailing_id']}: missing {', '.join(missing)}")
                continue

            render_token = row.get("render_token")
            if should_prepare_render_urls:
                render_token = ensure_render_token(conn, row["enrollment_mailing_id"])

            render_url = build_render_url(row["enrollment_mailing_id"], render_token) if render_token else None
            row["render_url"] = render_url
            mailing = as_mailing(row)

            if dry_run:
                render_url_summary = render_url or "not prepared; rerun with --prepare-render-urls to create a testable URL"
                print(
                    "Dry run planned mailing "
                    f"{row['enrollment_mailing_id']}, enrollment {row['enrollment_id']}, "
                    f"mailing {mailing['id']}: "
                    f"{build_campaign_merge_variables(contact, campaign, mailing)}, "
                    f"render_url={render_url_summary}"
                )
                continue

            delivery = ensure_delivery(conn, row)
            if delivery["status"] == "sent":
                skipped_count += 1
                print(f"Skipping delivery {delivery['id']}: already sent")
                continue

            try:
                created_letter = send_campaign_mailing(contact, campaign, mailing)
                provider_id = getattr(created_letter, "id", created_letter)
                mark_delivery_sent(conn, row, delivery["id"], provider_id)
                advance_enrollment(conn, row)
                sent_count += 1
                print(f"Sent delivery {delivery['id']}: {provider_id}")
            except ApiException as exc:
                failed_count += 1
                mark_delivery_failed(conn, row, delivery["id"], exc)
                print(f"Failed delivery {delivery['id']}: {exc}")
            except Exception as exc:
                failed_count += 1
                mark_delivery_failed(conn, row, delivery["id"], exc)
                print(f"Failed delivery {delivery['id']}: {exc}")
    finally:
        conn.close()

    print(
        "Summary: "
        f"sent={sent_count}, skipped={skipped_count}, failed={failed_count}, dry_run={dry_run}"
    )


if __name__ == "__main__":
    run()
