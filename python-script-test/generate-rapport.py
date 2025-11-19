import requests

# L'URL de votre endpoint Laravel
url = f"http://127.0.0.1:8000/api/generate-token-rapport-cerfa"


# Créez le dictionnaire des en-têtes
headers_no_token = {
    "secret-token": "Admin",
    "Content-Type": "application/json"
}

body_devis = {
    'client': "Apple",
    'document': "rapport_intervention",
    'uid': "Reparation-Iphone14-6744",
    'code_client': "code",
    'nom_client' : "nom client",
    'email_client' : "email client",
    'telephone_client' : "Telephone client",
    'portable_client' : "Portable client",
    'adresse_facturation' : "Adresse facturation",
    'cp_facturation' : "cp facturation",
    'ville_facturation' : "Ville facturation",
    'lieu_intervention' : "Lieu intervention",
    'adresse_intervention' : "Adresse intervention",
    'cp_intervention' : "cp intervention",
    'ville_intervention' : "ville intervention",
    'intervenant' : "intervenant",
    'description' : "description"
}

try:
    response = requests.post(url, json=body_devis, headers=headers_no_token) # Utilisez la même méthode (GET/POST) que votre route

    print(f"URL de la requête : {url}")
    print(f"Code de statut HTTP : {response.status_code}")
    print(f"Type de contenu de la réponse : {response.headers.get('Content-Type')}")

    if 'application/json' in response.headers.get('Content-Type', ''):
        print("Réponse JSON :", response.json())
    else:
        print("Réponse non JSON (HTML probable) :")
        print(response.text[:500]) # Affiche les 150 000 premiers caractères

except requests.exceptions.ConnectionError as e:
    print(f"Erreur de connexion : Assurez-vous que le serveur Laravel est lancé et accessible à {url.split('/api')[0] if '/api' in url else url.split('/open')[0]}")
except requests.exceptions.RequestException as e:
    print(f"Une erreur est survenue lors de la requête : {e}")