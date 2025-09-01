<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Formulaire POST auto-submit avec inputs text</title>
</head>
<body>
    <h1>Formulaire POST auto-submit avec inputs text</h1>

    <form action="http://127.0.0.1:8000/create-json" method="post" id="dataForm">
        @csrf
        <!-- Tous les champs en input text -->
        <input type="text" name="client" value="FakeClient123" />
        <input type="text" name="document" value="rapport_intervention" />
        <input type="text" name="uid" value="UID789" />

        <input type="text" name="code_client" value="C12345" />
        <input type="text" name="nom_client" value="Dupont" />
        <input type="text" name="email_client" value="dupont@example.com" />
        <input type="text" name="telephone_client" value="0102030405" />
        <input type="text" name="portable_client" value="0607080910" />
        <input type="text" name="adresse_facturation" value="1 rue de Paris" />
        <input type="text" name="cp_facturation" value="75001" />
        <input type="text" name="ville_facturation" value="Paris" />
        <input type="text" name="lieu_intervention" value="Site A" />
        <input type="text" name="adresse_intervention" value="2 avenue des Champs" />
        <input type="text" name="cp_intervention" value="75002" />
        <input type="text" name="ville_intervention" value="Paris" />
        <input type="text" name="intervenant" value="Jean Martin" />
        <input type="text" name="description" value="Description fictive de l'intervention." />

        <button type="submit">Envoyer</button>
    </form>

    <script>
        /*
        window.onload = function() {
            document.getElementById('dataForm').submit();
        };*/
    </script>
</body>
</html>
