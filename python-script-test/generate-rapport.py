import requests

# L'URL de votre endpoint Laravel
url = f"http://127.0.0.1:8000/api/create-json"


# Créez le dictionnaire des en-têtes
headers_no_token = {
    "secret-token": "Admin",  # Remplacez par votre token si nécessaire
    "Content-Type": "application/json"
}

body_devis = {
    'client': "UpDéjeuner",
    'document': "rapport_intervention",
    'uid': "bbb", 
    'code_client': "Code client",
    'nom_client' : "UP",
    'email_client' : "up@dejeuner.com",
    'telephone_client' : "06 66 66 26 36",
    'portable_client' : "03 33 33 23 63",
    'adresse_facturation' : "11 rue de la paix",
    'cp_facturation' : "75000",
    'ville_facturation' : "Paris",
    'lieu_intervention' : "Restaurant",
    'adresse_intervention' : "24 rue de la paix",
    'cp_intervention' : "75000",
    'ville_intervention' : "Paris",
    'date_intervention' : "2024-06-20",
    'intervenant' : "James Blonde",
    'description' : "Intervention de maintenance préventive sur les équipements de cuisine. Vérification des systèmes de ventilation, nettoyage des filtres, et contrôle des dispositifs de sécurité. Aucune anomalie majeure détectée, mais recommandation de remplacer les filtres dans les 6 mois pour assurer une performance optimale.",
}

try:
    response = requests.post(url, json=body_devis, headers=headers_no_token) # Utilisez la même méthode (GET/POST) que votre route

    print(f"URL de la requête : {url}")
    print(f"Code de statut HTTP : {response.status_code}")
    print(f"Type de contenu de la réponse : {response.headers.get('Content-Type')}")

    if 'application/json' in response.headers.get('Content-Type', ''):
        print("Réponse JSON :", response.json())
    else:
        print("Code de statut HTTP : ", response.status_code)
        print("Réponse non JSON (HTML probable) :")
        print(response.text[:500]) # Affiche les 150 000 premiers caractères

except requests.exceptions.ConnectionError as e:
    print(f"Erreur de connexion : Assurez-vous que le serveur Laravel est lancé et accessible à {url.split('/api')[0] if '/api' in url else url.split('/open')[0]}")
except requests.exceptions.RequestException as e:
    print(f"Une erreur est survenue lors de la requête : {e}")