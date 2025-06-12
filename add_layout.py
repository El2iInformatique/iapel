import sqlite3

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


db_file = "./database/database.sqlite"

nomClient = input("Nom du client : ")
nombreItem = int(input("Nombre d'item : "))
nomLayout = nomClient + "_LAYOUT"


insert_data_query = "INSERT INTO layout_client (nom_client, nom_layout) VALUES (?, ?);"
requete_sqlite(db_file, insert_data_query, (nomClient, nomLayout))
print("Données insérées.")



nom_fichier_w = "./resources/views/custom/" + nomLayout + ".blade.php"
donneeBase = ""

for i in range(1, nombreItem + 1):
    donneeBase += f""" <div class="mb-3">

        <label for="telephone" class="form-label">Label question</label>  <! -- Affichage question -->
        <input type="tel" class="form-control" name="item{i}">  <! -- Le input de la question -->
        <input type="text" name="question-item{i}" value="Mettre une question" hidden>  <! -- La question (normalement la même que le label), c'est ce qui sera affiché dans le rapport -->

    </div>
    """

try:
    with open(nom_fichier_w, 'w', encoding='utf-8') as f:
        f.write("\n")
        f.write(donneeBase)
    print(f"Fichier '{nom_fichier_w}' créé et écrit avec succès en mode 'w'.")
except IOError as e:
    print(f"Erreur lors de la création/écriture du fichier : {e}")