from mysql.connector import MySQLConnection

from settings import DB_CONNECTION, DB_DATABASE, DB_HOST, DB_PASSWORD, DB_PORT, DB_USERNAME


def read_db_config():
    if DB_CONNECTION != "mysql":
        raise RuntimeError(
            "The POD mailer integration requires DB_CONNECTION=mysql "
            f"but Laravel is configured for DB_CONNECTION={DB_CONNECTION}."
        )

    if not DB_DATABASE or not DB_USERNAME:
        raise RuntimeError("Missing Laravel database settings: DB_DATABASE and DB_USERNAME are required.")

    return {
        "host": DB_HOST,
        "port": DB_PORT,
        "database": DB_DATABASE,
        "user": DB_USERNAME,
        "password": DB_PASSWORD,
    }


def get_connection():
    return MySQLConnection(**read_db_config())
