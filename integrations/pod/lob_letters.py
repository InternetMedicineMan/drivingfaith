import lob_python
from lob_python.api.letters_api import LettersApi
from lob_python.model.address_editable import AddressEditable
from lob_python.model.letter_editable import LetterEditable
from lob_python.model.ltr_use_type import LtrUseType
from lob_python.model.merge_variables import MergeVariables

from settings import LOB_API_KEY, LOB_FROM_ADDRESS_ID, LOB_TEMPLATE_ID


def build_contact_merge_variables(contact):
    return {
        "first_name": contact["first_name"] or "",
        "last_name": contact["last_name"] or "",
        "address1": contact["address1"] or "",
        "address2": contact["address2"] or "",
        "city": contact["city"] or "",
        "state": contact["state"] or "",
        "zip": contact["zip"] or "",
    }


def build_campaign_merge_variables(contact, campaign, mailing):
    merge_variables = build_contact_merge_variables(contact)
    merge_variables.update(
        {
            "campaign_name": campaign["name"] or "",
            "mailing_name": mailing["name"] or "",
            "mailing_sequence": mailing["sequence"],
        }
    )

    return merge_variables


def build_contact_address(contact):
    first_name = contact["first_name"] or ""
    last_name = contact["last_name"] or ""
    full_name = f"{first_name} {last_name}".strip()

    address = AddressEditable(
        name=full_name,
        address_line1=contact["address1"],
        address_line2=contact.get("address2") or None,
        address_city=contact["city"],
        address_state=contact["state"],
        address_zip=contact["zip"],
    )

    return address


def send_intro_letter(contact):
    if not LOB_API_KEY:
        raise RuntimeError("Missing Lob API key. Set LOB_API_KEY or [lob] api_key in config.ini.")

    configuration = lob_python.Configuration(username=LOB_API_KEY)

    letter_editable = LetterEditable(
        description="Driving Faith Intro Bible Study",
        file=LOB_TEMPLATE_ID,
        color=False,
        double_sided=True,
        address_placement="top_first_page",
        to=build_contact_address(contact),
        _from=LOB_FROM_ADDRESS_ID,
        merge_variables=MergeVariables(**build_contact_merge_variables(contact)),
        return_envelope=True,
        perforated_page=1,
        use_type=LtrUseType("marketing"),
    )

    with lob_python.ApiClient(configuration) as api_client:
        api = LettersApi(api_client)
        return api.create(letter_editable)


def send_campaign_mailing(contact, campaign, mailing):
    if not LOB_API_KEY:
        raise RuntimeError("Missing Lob API key. Set LOB_API_KEY or [lob] api_key in config.ini.")

    mailing_file = mailing.get("rendered_html") or mailing.get("provider_template_id")
    if not mailing_file:
        raise RuntimeError(f"Mailing {mailing['id']} is missing rendered_html or provider_template_id.")

    configuration = lob_python.Configuration(username=LOB_API_KEY)

    letter_editable = LetterEditable(
        description=f"{campaign['name']} - {mailing['name']}",
        file=mailing_file,
        color=bool(mailing["color"]),
        double_sided=bool(mailing["double_sided"]),
        address_placement=mailing["address_placement"] or "top_first_page",
        to=build_contact_address(contact),
        _from=LOB_FROM_ADDRESS_ID,
        merge_variables=MergeVariables(**build_campaign_merge_variables(contact, campaign, mailing)),
        return_envelope=bool(mailing["return_envelope"]),
        perforated_page=mailing["perforated_page"],
        use_type=LtrUseType(mailing["mail_class"] or "marketing"),
    )

    with lob_python.ApiClient(configuration) as api_client:
        api = LettersApi(api_client)
        return api.create(letter_editable)
