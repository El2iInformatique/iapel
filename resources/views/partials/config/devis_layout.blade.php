<div class="accordion-item mb-3 border-0 shadow-sm rounded-3">
    <h2 class="accordion-header" id="heading_devis_commercial">
        <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_devis_commercial" aria-expanded="true" aria-controls="collapse_devis_commercial">
            <i class="bi bi-currency-euro me-2"></i> Conditions commerciales
        </button>
    </h2>
    <div id="collapse_devis_commercial" class="accordion-collapse collapse show" aria-labelledby="heading_devis_commercial" data-bs-parent="#accordion_parametres_devis">
        <div class="accordion-body">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="devis_tva_defaut" class="form-label">Taux de TVA par défaut (%) :</label>
                    <select class="form-input form-control" id="devis_tva_defaut" name="devis_tva_defaut">
                        <option value="20" {{ (isset($config['devis_tva_defaut']) && $config['devis_tva_defaut'] == '20') ? 'selected' : '' }}>20 %</option>
                        <option value="10" {{ (isset($config['devis_tva_defaut']) && $config['devis_tva_defaut'] == '10') ? 'selected' : '' }}>10 %</option>
                        <option value="5.5" {{ (isset($config['devis_tva_defaut']) && $config['devis_tva_defaut'] == '5.5') ? 'selected' : '' }}>5.5 %</option>
                        <option value="0" {{ (isset($config['devis_tva_defaut']) && $config['devis_tva_defaut'] == '0') ? 'selected' : '' }}>0 % (Auto-entrepreneur)</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="devis_validite" class="form-label">Validité du devis (en jours) :</label>
                    <input type="number" class="form-input form-control" id="devis_validite" name="devis_validite" value="{{ $config['devis_validite'] ?? '30' }}" min="1">
                </div>
            </div>

            <div class="mb-3">
                <label for="devis_rib" class="form-label">Coordonnées bancaires (RIB/IBAN) pour acompte :</label>
                <textarea class="form-input form-control" id="devis_rib" name="devis_rib" rows="3" placeholder="Saisissez ici l'IBAN et le BIC à afficher sur le devis...">{{ $config['devis_rib'] ?? '' }}</textarea>
            </div>

            <div class="mb-3">
                <label for="devis_cgv" class="form-label">Conditions de paiement / CGV réduites :</label>
                <textarea class="form-input form-control" id="devis_cgv" name="devis_cgv" rows="4" placeholder="Ex: Acompte de 30% à la signature, solde à la fin des travaux...">{{ $config['devis_cgv'] ?? '' }}</textarea>
            </div>
            
        </div>
    </div>
</div>