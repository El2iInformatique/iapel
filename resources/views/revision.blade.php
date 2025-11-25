<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Reconnaissance des végétaux</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

    <style>
        body {
            font-size: 1.2rem;
        }
        .card-header {
            font-size: 1.1rem;
        }

        .card-header .label-small {
            font-size: .8rem;
            font-weight: 400;
        }

        .question-nom {
            font-size: 1.6rem;
            text-align: center;
            display: block;
            font-weight: 600;
        }
        .question-help {
            font-size: .9rem;
        }

        select.form-select,
        input.form-control {
            font-size: 1.1rem;
            padding: 0.6rem;
        }
        label.form-label {
            font-size: 1.1rem;
        }
        button.btn {
            font-size: 1.1rem;
            padding: 0.6rem 1.2rem;
        }

        .mode-buttons .btn,
        .direction-buttons .btn {
            font-size: 0.9rem;
            line-height: 1.2rem;
        }

        .mode-active {
            font-weight: 600;
        }
        
        .btn-reveal{
            padding:.35rem .6rem;
            font-size:.9rem
        }

    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">

            <div class="card shadow-sm">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 flex-wrap">

                    <div class="d-flex flex-column gap-1">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-flower1"></i>
                            <span>Reconnaissance des végétaux</span>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2 text-md-end">
                        <!-- Choix du mode de difficulté -->
                        <div class="mode-buttons d-flex gap-2 flex-wrap justify-content-md-end">
                            <a href="{{ route('revision.index', ['mode' => 'simple', 'direction' => $direction]) }}"
                               class="btn btn-sm {{ $mode === 'simple' ? 'btn-primary mode-active' : 'btn-outline-primary' }}">
                                <i class="bi bi-hand-index-thumb"></i>
                                Simple
                            </a>

                            <a href="{{ route('revision.index', ['mode' => 'difficile', 'direction' => $direction]) }}"
                               class="btn btn-sm {{ $mode === 'difficile' ? 'btn-danger mode-active' : 'btn-outline-danger' }}">
                                <i class="bi bi-pencil-square"></i>
                                Difficile
                            </a>
                        </div>

                        <!-- Choix du paquet -->
                        <div class="d-flex gap-2 flex-wrap justify-content-md-end">
                            <a href="{{ route('revision.index', ['mode' => $mode, 'direction' => $direction, 'pack' => 'all']) }}"
                            class="btn btn-sm {{ $pack === 'all' ? 'btn-success mode-active' : 'btn-outline-success' }}">
                                Tous
                            </a>
                            <a href="{{ route('revision.index', ['mode' => $mode, 'direction' => $direction, 'pack' => 'g1']) }}"
                            class="btn btn-sm {{ $pack === 'g1' ? 'btn-success mode-active' : 'btn-outline-success' }}">
                                Groupe 1
                            </a>
                            <a href="{{ route('revision.index', ['mode' => $mode, 'direction' => $direction, 'pack' => 'g2']) }}"
                            class="btn btn-sm {{ $pack === 'g2' ? 'btn-success mode-active' : 'btn-outline-success' }}">
                                Groupe 2
                            </a>
                            <a href="{{ route('revision.index', ['mode' => $mode, 'direction' => $direction, 'pack' => 'g3']) }}"
                            class="btn btn-sm {{ $pack === 'g3' ? 'btn-success mode-active' : 'btn-outline-success' }}">
                                Groupe 3
                            </a>
                        </div>

                        <!-- Choix du sens (direction) -->
                        <div class="direction-buttons d-flex gap-2 flex-wrap justify-content-md-end">
                            <a href="{{ route('revision.index', ['mode' => $mode, 'direction' => 'normal']) }}"
                               class="btn btn-sm {{ $direction === 'normal' ? 'btn-secondary mode-active' : 'btn-outline-secondary' }}">
                                <i class="bi bi-arrow-right-circle"></i>
                                Mode 1
                            </a>

                            <a href="{{ route('revision.index', ['mode' => $mode, 'direction' => 'inverse']) }}"
                               class="btn btn-sm {{ $direction === 'inverse' ? 'btn-secondary mode-active' : 'btn-outline-secondary' }}">
                                <i class="bi bi-arrow-left-circle"></i>
                                Mode 2
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">

                    {{-- Message de feedback --}}
                    @if ($verdict === 'ok')
                    <div id="bravoMessage"
                        class="alert alert-success d-flex align-items-center"
                        role="alert"
                        data-next-url="{{ route('revision.index', ['mode' => $mode, 'direction' => $direction, 'pack' => $pack]) }}">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <div>Bravo ! 🎉 C'est correct.</div>
                    </div>

                    @elseif ($verdict === 'ko')
                    <div class="alert alert-danger" role="alert">
                        <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-x-circle-fill me-2"></i>
                        <div>Pas encore 😅 Réessaie !</div>
                        </div>

                        
                        <div class="mt-2 ps-4">
                            @if ($direction === 'normal')
                            @foreach ([
                                'Famille' => $etat_famille,
                                'Espèce' => $etat_espece,
                                'Sous-espèce' => $etat_sous_espece
                            ] as $label => $etat)
                                <div class="d-flex justify-content-between">
                                <span>{{ $label }} :</span>
                                @if ($etat === 'ok')
                                    <strong class="text-success">✔ correct</strong>
                                @elseif ($etat === 'presque')
                                    <strong class="text-warning">⚠ presque juste</strong>
                                @else
                                    <strong class="text-danger">✘ faux</strong>
                                @endif
                                </div>
                            @endforeach
                            @else
                            <div class="d-flex justify-content-between">
                                <span>Nom courant :</span>
                                @if ($etat_nom === 'ok')
                                <strong class="text-success">✔ correct</strong>
                                @elseif ($etat_nom === 'presque')
                                <strong class="text-warning">⚠ presque juste</strong>
                                @else
                                <strong class="text-danger">✘ faux</strong>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    @else
                    <div class="alert alert-info" role="alert">
                        @if ($direction === 'normal')
                        @if ($mode === 'simple')
                            Choisis la <strong>famille</strong>, l’<strong>espèce</strong> et la <strong>sous-espèce</strong>.
                        @else
                            Écris la <strong>famille</strong>, l’<strong>espèce</strong> et la <strong>sous-espèce</strong>.
                        @endif
                        @else
                        @if ($mode === 'simple')
                            Choisis le <strong>nom courant</strong> correspondant.
                        @else
                            Écris le <strong>nom courant</strong>.
                        @endif
                        @endif
                    </div>
                    @endif
                    

                    <!-- Question affichée -->
                    <div class="mb-4 text-center">
                        <div class="text-primary question-nom">
                            {{ $question }}
                        </div>
                    </div>

                    <!-- Formulaire -->
                    <form method="POST" action="{{ route('revision.check') }}" autocomplete="off">
                        @csrf

                        <!-- garder le contexte -->
                        <input type="hidden" name="mode" value="{{ $mode }}">
                        <input type="hidden" name="pack" value="{{ $pack }}">
                        <input type="hidden" name="direction" value="{{ $direction }}">
                        <input type="hidden" name="question" value="{{ $question }}">

                        <!-- on garde aussi les solutions attendues pour vérifier -->
                        <input type="hidden" name="solution_famille" value="{{ $solution_famille }}">
                        <input type="hidden" name="solution_espece" value="{{ $solution_espece }}">
                        <input type="hidden" name="solution_sous_espece" value="{{ $solution_sous_espece }}">
                        <input type="hidden" name="solution_nom" value="{{ $solution_nom }}">


                        <div class="row g-3">

                            @if ($direction === 'normal')
                                {{-- DIRECTION NORMALE : trouver famille / espece / sous-espece --}}

                                @if ($mode === 'simple')
                                    {{-- mode simple = selects --}}

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Famille</label>
                                        <select class="form-select" name="famille" required>
                                            <option value="">— Choisir —</option>
                                            @foreach ($familles as $famille)
                                                <option value="{{ $famille }}"
                                                    @if($choix_famille === $famille) selected @endif>
                                                    {{ $famille }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Espèce</label>
                                        <select class="form-select" name="espece" required>
                                            <option value="">— Choisir —</option>
                                            @foreach ($especes as $espece)
                                                <option value="{{ $espece }}"
                                                    @if($choix_espece === $espece) selected @endif>
                                                    {{ $espece }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Sous-espèce</label>
                                        <select class="form-select" name="sous_espece" required>
                                            <option value="">— Choisir —</option>
                                            @foreach ($sousEspeces as $ss)
                                                <option value="{{ $ss }}"
                                                    @if($choix_sous_espece === $ss) selected @endif>
                                                    {{ $ss }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                @else
                                    {{-- mode difficile = saisie libre --}}

                                    {{-- Famille --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Famille</label>
                                        <div class="input-group">
                                        <input
                                            id="inpFamille"
                                            type="text"
                                            class="form-control"
                                            name="famille"
                                            value="{{ $choix_famille }}"
                                            placeholder="Ex : Fagaceae"
                                            required
                                            autocomplete="off"
                                        >
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-reveal"
                                                data-target="#inpFamille"
                                                data-value="{{ $solution_famille }}">
                                            <i class="bi bi-eye"></i> 
                                        </button>
                                        </div>
                                    </div>

                                    {{-- Espèce --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Espèce</label>
                                        <div class="input-group">
                                        <input
                                            id="inpEspece"
                                            type="text"
                                            class="form-control"
                                            name="espece"
                                            value="{{ $choix_espece }}"
                                            placeholder="Ex : Quercus"
                                            required
                                            autocomplete="off"
                                        >
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-reveal"
                                                data-target="#inpEspece"
                                                data-value="{{ $solution_espece }}">
                                            <i class="bi bi-eye"></i> 
                                        </button>
                                        </div>
                                    </div>

                                    {{-- Sous-espèce --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Sous-espèce</label>
                                        <div class="input-group">
                                        <input
                                            id="inpSousEspece"
                                            type="text"
                                            class="form-control"
                                            name="sous_espece"
                                            value="{{ $choix_sous_espece }}"
                                            placeholder="Ex : robur"
                                            required
                                            autocomplete="off"
                                        >
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-reveal"
                                                data-target="#inpSousEspece"
                                                data-value="{{ $solution_sous_espece }}">
                                            <i class="bi bi-eye"></i> 
                                        </button>
                                        </div>
                                    </div>

                                @endif

                            @else
                                {{-- DIRECTION INVERSE : trouver le NOM COURANT --}}

                                @if ($mode === 'simple')
                                    {{-- mode simple = select d'un nom courant --}}

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Nom courant</label>
                                        <select class="form-select" name="nom_courant" required>
                                            <option value="">— Choisir —</option>
                                            @foreach ($noms as $nom)
                                                <option value="{{ $nom }}"
                                                    @if($choix_nom === $nom) selected @endif>
                                                    {{ $nom }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                @else
                                    {{-- mode difficile = saisie libre du nom courant --}}

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Nom courant</label>
                                        <div class="input-group">
                                        <input
                                            id="inpNomCourant"
                                            type="text"
                                            class="form-control"
                                            name="nom_courant"
                                            value="{{ $choix_nom }}"
                                            placeholder="Ex : Chêne rouge d'Amérique"
                                            required
                                            autocomplete="off"
                                        >
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-reveal"
                                                data-target="#inpNomCourant"
                                                data-value="{{ $solution_nom }}">
                                            <i class="bi bi-eye"></i> 
                                        </button>
                                        </div>
                                    </div>

                                @endif

                            @endif

                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i>
                                Vérifier
                            </button>

                            <a href="{{ route('revision.index', ['mode' => $mode, 'direction' => $direction, 'pack' => $pack]) }}"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-shuffle"></i>
                                Nouvelle plante
                            </a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bravo = document.getElementById('bravoMessage');
        if (bravo) {
            const nextUrl = bravo.dataset.nextUrl; // fourni par Blade
            setTimeout(() => {
                window.location.href = nextUrl;
            }, 2000);
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-reveal').forEach(btn => {
        btn.addEventListener('click', () => {
        const targetSel = btn.getAttribute('data-target');
        const value = btn.getAttribute('data-value') || '';
        const input = document.querySelector(targetSel);
        if (!input) return;
        input.value = value;

        // Feedback visuel léger (optionnel)
        input.classList.remove('is-invalid','is-warning');
        input.classList.add('is-valid');

        // place le curseur à la fin
        input.focus();
        const len = input.value.length;
        input.setSelectionRange(len, len);
        });
    });
    });
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
