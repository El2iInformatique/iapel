import os
import ctypes
import sys
import sqlite3

def is_admin():
    try:
        # Pour les systèmes Linux, macOS
        # getuid() renvoie 0 si l'utilisateur est root
        return os.getuid() == 0
    except AttributeError:
        # Pour Windows
        # IsUserAnAdmin() est une fonction de l'API Windows
        # qui vérifie si l'utilisateur actuel est un administrateur.
        try:
            return ctypes.windll.shell32.IsUserAnAdmin() != 0
        except Exception:
            # En cas d'erreur (par exemple, si ctypes n'est pas disponible ou l'API échoue)
            return False


def requete_sqlite(db_name, query, params=None):
    conn = None
    try:
        conn = sqlite3.connect(db_name)
        cursor = conn.cursor()

        if params:
            cursor.execute(query, params)
        else:
            cursor.execute(query)

        if query.strip().upper().startswith("SELECT"):
            return cursor.fetchall()
        else:
            conn.commit()
            return None

    except sqlite3.Error as e:
        print(f"Erreur SQLite: {e}")
        return None
    finally:
        if conn:
            conn.close()







if not is_admin():
    print("Veuillez lancer se script en administrateur")
    sys.exit()



db_file = "./database/database.sqlite"