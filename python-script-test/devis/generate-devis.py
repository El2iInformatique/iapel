import requests

# L'URL de votre endpoint Laravel
url = f"http://127.0.0.1:8000/api/generate-token"


# Créez le dictionnaire des en-têtes
headers_no_token = {
    "secret-token": "Admin",
    "Content-Type": "application/json"
}

body_devis = {
    'organisation_id': 'Id',
    'devis_id': '1205.2',
    'tiers': 'tiers',
    'client_email': 'client_email',
    'titre': 'titre',
    'montant_HT': 'montant_HT',
    'montant_TVA': 'montant_TVA',
    'montant_TTC': 'montant_TTC',
    'coords': 'coords',
    'nb_pages': 'nb_pages'
}

try:
    response = requests.get(url, json=body_devis, headers=headers_no_token) # Utilisez la même méthode (GET/POST) que votre route

    print(f"URL de la requête : {url}")
    print(f"Code de statut HTTP : {response.status_code}")
    print(f"Type de contenu de la réponse : {response.headers.get('Content-Type')}")

    if 'application/json' in response.headers.get('Content-Type', ''):
        print("Réponse JSON :", response.json())
    else:
        print("Réponse non JSON (HTML probable) :")
        print(response.text[:150000]) # Affiche les 500 premiers caractères

except requests.exceptions.ConnectionError as e:
    print(f"Erreur de connexion : Assurez-vous que le serveur Laravel est lancé et accessible à {url.split('/api')[0] if '/api' in url else url.split('/open')[0]}")
except requests.exceptions.RequestException as e:
    print(f"Une erreur est survenue lors de la requête : {e}")