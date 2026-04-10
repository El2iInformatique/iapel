<div class="accordion-item mb-3 border-0 shadow-sm rounded-3">
    <h2 class="accordion-header" id="heading_cerfa_operateur">
        <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_cerfa_operateur" aria-expanded="true" aria-controls="collapse_cerfa_operateur">
            <i class="bi bi-building me-2"></i> Informations de l'Opérateur (CERFA)
        </button>
    </h2>
    <div id="collapse_cerfa_operateur" class="accordion-collapse collapse show" aria-labelledby="heading_cerfa_operateur" data-bs-parent="#accordion_parametres_cerfa">
        <div class="accordion-body">
            <div class="alert alert-info py-2 mb-4">
                <i class="bi bi-info-circle-fill me-2"></i> Ces informations apparaîtront dans la section [1] de tous les formulaires CERFA 15497.
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nom" class="form-label">Nom de l'entreprise :</label>
                    <input type="text" class="form-input form-control" id="nom" name="nom" value="{{ $config['nom'] ?? '' }}" maxlength="30" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="siret" class="form-label">Numéro SIRET :</label>
                    <input type="text" class="form-input form-control" id="siret" name="siret" value="{{ $config['siret'] ?? '' }}" maxlength="14" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="adresse" class="form-label">Adresse complète :</label>
                <input type="text" class="form-input form-control" id="adresse" name="adresse" value="{{ $config['adresse'] ?? '' }}" maxlength="60" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="numeroAttestationCapacite" class="form-label">Numéro d'attestation de capacité :</label>
                    <input type="text" class="form-input form-control" id="numeroAttestationCapacite" name="numeroAttestationCapacite" value="{{ $config['numeroAttestationCapacite'] ?? '' }}" maxlength="25" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="identificationControle" class="form-label">Identification du contrôle :</label>
                    <input type="text" class="form-input form-control" id="identificationControle" name="identificationControle" value="{{ $config['identificationControle'] ?? '' }}" maxlength="20">
                </div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3 text-muted">Contrôle matériel & Signataire par défaut</h5>

            <div class="mb-3">
                <label for="controleMaterielDate" class="form-label">Date de contrôle du matériel :</label>
                <input type="date" class="form-input form-control" id="controleMaterielDate" name="controleMaterielDate" value="{{ $config['controleMaterielDate'] ?? '' }}">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="OperateurSignataireNom" class="form-label">Nom du signataire :</label>
                    <input type="text" class="form-input form-control" id="OperateurSignataireNom" name="OperateurSignataireNom" value="{{ $config['OperateurSignataireNom'] ?? '' }}" maxlength="27" placeholder="Ex: Jean Dupont">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="OperateurSignataireQualiter" class="form-label">Qualité du signataire :</label>
                    <input type="text" class="form-input form-control" id="OperateurSignataireQualiter" name="OperateurSignataireQualiter" value="{{ $config['OperateurSignataireQualiter'] ?? '' }}" maxlength="27" placeholder="Ex: Gérant, Technicien...">
                </div>
            </div>
        </div>
    </div>
</div>