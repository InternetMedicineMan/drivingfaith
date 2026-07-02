import os
from pathlib import Path


INTEGRATION_ROOT = Path(__file__).resolve().parent


def find_laravel_root(start=INTEGRATION_ROOT):
    for path in (start, *start.parents):
        if (path / "artisan").is_file() and (path / ".env").is_file():
            return path
    return start


LARAVEL_ROOT = find_laravel_root()
LARAVEL_ENV_FILE = Path(os.getenv("DRIVINGFAITH_ENV_FILE", LARAVEL_ROOT / ".env"))


def _strip_quotes(value):
    if len(value) >= 2 and value[0] == value[-1] and value[0] in ("'", '"'):
        return value[1:-1]
    return value


def _read_env_file(path):
    values = {}
    if not path.is_file():
        return values

    for raw_line in path.read_text().splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue

        key, value = line.split("=", 1)
        values[key.strip()] = _strip_quotes(value.strip())

    return values


_ENV_FILE_VALUES = _read_env_file(LARAVEL_ENV_FILE)


def get_config_value(env_name, default=None, required=False):
    if os.getenv(env_name) not in (None, ""):
        return os.getenv(env_name)

    if _ENV_FILE_VALUES.get(env_name) not in (None, ""):
        return _ENV_FILE_VALUES[env_name]

    if required:
        raise RuntimeError(f"Missing required setting: {env_name}")

    return default


def get_int_config_value(env_name, default):
    return int(get_config_value(env_name, default=str(default)))


DB_CONNECTION = get_config_value("DB_CONNECTION", default="mysql")
DB_HOST = get_config_value("DB_HOST", default="127.0.0.1")
DB_PORT = get_int_config_value("DB_PORT", 3306)
DB_DATABASE = get_config_value("DB_DATABASE", default="")
DB_USERNAME = get_config_value("DB_USERNAME", default="")
DB_PASSWORD = get_config_value("DB_PASSWORD", default="")

LOB_API_KEY = get_config_value("LOB_API_KEY")
LOB_PUBLIC_DOMAIN = get_config_value("LOB_PUBLIC_DOMAIN", default=get_config_value("APP_URL", default="")).rstrip("/")
LOB_TEMPLATE_ID = get_config_value("LOB_INTRO_TEMPLATE_ID", default="tmpl_a36258afb8ff682")
LOB_FROM_ADDRESS_ID = get_config_value("LOB_FROM_ADDRESS_ID", default="adr_3d00b791666b3b2a")

POD_SOURCE_ID = get_int_config_value("POD_SOURCE_ID", 3)
POD_BATCH_LIMIT = get_int_config_value("POD_BATCH_LIMIT", 25)
