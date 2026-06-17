import argparse

from lob_python.exceptions import ApiException

from db import get_connection
from pod.lob_letters import build_contact_merge_variables, send_intro_letter
from settings import POD_BATCH_LIMIT, POD_SOURCE_ID


REQUIRED_FIELDS = ("first_name", "last_name", "address1", "city", "state", "zip")


def fetch_pending_contacts(conn, limit):
    query = """
        SELECT id, first_name, last_name, address1, address2, city, state, zip
        FROM drive_contacts
        WHERE source_id = %s
            AND complete = 0
        ORDER BY id
    """
    params = [POD_SOURCE_ID]

    if limit is not None:
        query += " LIMIT %s"
        params.append(limit)

    cursor = conn.cursor(dictionary=True)
    cursor.execute(query, tuple(params))
    return cursor.fetchall()


def mark_complete(conn, contact_id):
    cursor = conn.cursor()
    cursor.execute(
        """
        UPDATE drive_contacts
        SET complete = 1
        WHERE id = %s
        """,
        (contact_id,),
    )
    conn.commit()


def missing_required_fields(contact):
    return [field for field in REQUIRED_FIELDS if not contact.get(field)]


def parse_args():
    parser = argparse.ArgumentParser(description="Send queued Driving Faith intro letters.")
    batch_group = parser.add_mutually_exclusive_group()
    batch_group.add_argument(
        "--limit",
        type=int,
        default=POD_BATCH_LIMIT,
        help=f"Maximum number of pending contacts to process. Defaults to {POD_BATCH_LIMIT}.",
    )
    batch_group.add_argument(
        "--all",
        action="store_true",
        help="Process all pending contacts that match the source and complete filters.",
    )
    parser.add_argument(
        "--send",
        action="store_true",
        help="Send real letters. Without this flag, the script runs as a dry run.",
    )
    args = parser.parse_args()
    if args.limit is not None and args.limit < 1:
        parser.error("--limit must be 1 or greater")
    return args


def run():
    args = parse_args()
    limit = None if args.all else args.limit
    dry_run = not args.send

    sent_count = 0
    skipped_count = 0
    failed_count = 0

    conn = get_connection()
    try:
        contacts = fetch_pending_contacts(conn, limit)
        limit_label = "all" if limit is None else str(limit)
        print(
            f"Found {len(contacts)} pending contact(s) for source_id={POD_SOURCE_ID} "
            f"with limit={limit_label}."
        )

        for contact in contacts:
            missing = missing_required_fields(contact)
            if missing:
                skipped_count += 1
                print(f"Skipping contact {contact['id']}: missing {', '.join(missing)}")
                continue

            if dry_run:
                print(f"Dry run contact {contact['id']}: {build_contact_merge_variables(contact)}")
                continue

            try:
                created_letter = send_intro_letter(contact)
                mark_complete(conn, contact["id"])
                sent_count += 1
                print(f"Sent contact {contact['id']}: {getattr(created_letter, 'id', created_letter)}")
            except ApiException as exc:
                failed_count += 1
                print(f"Failed contact {contact['id']}: {exc}")
            except Exception as exc:
                failed_count += 1
                print(f"Failed contact {contact['id']}: {exc}")

    finally:
        conn.close()

    print(
        "Summary: "
        f"sent={sent_count}, skipped={skipped_count}, failed={failed_count}, dry_run={dry_run}"
    )


if __name__ == "__main__":
    run()
