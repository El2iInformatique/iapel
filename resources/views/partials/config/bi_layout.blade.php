<div class="accordion-item mb-3 border-0 shadow-sm rounded-3">
    <h2 class="accordion-header" id="heading_bi_general">
        <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_bi_general" aria-expanded="true" aria-controls="collapse_bi_general">
            <i class="bi bi-card-text me-2"></i> Informations par défaut
        </button>
    </h2>
    <div id="collapse_bi_general" class="accordion-collapse collapse show" aria-labelledby="heading_bi_general" data-bs-parent="#accordion_parametres_bi">
        <div class="accordion-body">
            <div class="mb-3">
                <label for="bi_email_default" class="form-label">Email de copie par défaut (ex: secrétariat) :</label>
                <input type="email" class="form-input form-control" id="bi_email_default" name="bi_email_default" value="{{ $config['bi_email_default'] ?? '' }}" placeholder="secretariat@monentreprise.com">
            </div>
            
            <div class="mb-3">
                <label for="bi_mentions_legales" class="form-label">Mentions légales en bas de page :</label>
                <textarea class="form-input form-control" id="bi_mentions_legales" name="bi_mentions_legales" rows="3" placeholder="Texte affiché en pied de page de chaque BI...">{{ $config['bi_mentions_legales'] ?? '' }}</textarea>
            </div>

            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="bi_afficher_prix" name="bi_afficher_prix" value="1" {{ isset($config['bi_afficher_prix']) && $config['bi_afficher_prix'] ? 'checked' : '' }}>
                <label class="form-check-label" for="bi_afficher_prix">
                    Afficher les tarifs des pièces sur le rapport d'intervention
                </label>
            </div>
        </div>
    </div>
</div>