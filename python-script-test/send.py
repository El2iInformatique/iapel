

'''
datas = {
    'client': 'Amazon',
    'document': 'rapport_intervention',
    'uid' : 'Test_Rapport',

    'code_client': 'un_code_client',
    'nom_client': 'un_nom_client',
    'email_client': 'un_email_client',
    'telephone_client': 'un_telephone_client',
    'portable_client': 'un_portable_client',
    'adresse_facturation': 'une_adresse_facturation',
    'cp_facturation': 'un_cp_facturation',
    'ville_facturation': 'une_ville_facturation',
    'lieu_intervention': 'un_lieu_intervention',
    'adresse_intervention': 'une_adresse_intervention',
    'cp_intervention': 'un_cp_intervention',
    'ville_intervention': 'une_ville_intervention',
    'intervenant': 'un_intervenant',
    'description': 'une_descritpion',
}
'''

import random
import string
import webbrowser
import requests
import time

def generate_random_string(length=60):
    """
    Génère une chaîne de caractères aléatoire de la longueur spécifiée.

    La chaîne contient des lettres majuscules, minuscules et des chiffres.
    """
    characters = string.ascii_letters + string.digits
    random_string = ''.join(random.choice(characters) for i in range(length))
    return random_string

nRequest = 0

while True : 
    randomToken = generate_random_string()
    url = f"http://127.0.0.1:8000/bi/{randomToken}"

    reponse = requests.get(url)    

    if reponse.status_code == 200:
        webbrowser.open(url)
    else :
        print('Url : ' + url + ", reponse" + str(reponse.status_code))
    
    nRequest += 1

    if nRequest == 10:
        time.sleep(60)
        nRequest = 0


