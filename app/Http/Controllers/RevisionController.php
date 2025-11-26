<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RevisionController extends Controller
{
    private function getPlantesData()
    {
        return [
            // ====== GROUPE 1  ======
            ['groupe'=>1,'nom'=>"Hêtre commun / Foyard",'famille'=>"Fagaceae",'espece'=>"Fagus",'sous_espece'=>"sylvatica"],
            ['groupe'=>1,'nom'=>"Charme / Charmille",'famille'=>"Betulaceae",'espece'=>"Carpinus",'sous_espece'=>"betulus"],
            ['groupe'=>1,'nom'=>"Chêne commun (ou pédonculé)",'famille'=>"Fagaceae",'espece'=>"Quercus",'sous_espece'=>"robur"],
            ['groupe'=>1,'nom'=>"Chêne des marais",'famille'=>"Fagaceae",'espece'=>"Quercus",'sous_espece'=>"palustris"],
            ['groupe'=>1,'nom'=>"Chêne rouge d'Amérique",'famille'=>"Fagaceae",'espece'=>"Quercus",'sous_espece'=>"rubra"],
            ['groupe'=>1,'nom'=>"Érable plane",'famille'=>"Aceraceae",'espece'=>"Acer",'sous_espece'=>"platanoïdes"],
            ['groupe'=>1,'nom'=>"Érable faux platane (sycomore)",'famille'=>"Aceraceae",'espece'=>"Acer",'sous_espece'=>"pseudoplatanus"],
            ['groupe'=>1,'nom'=>"Érable argenté",'famille'=>"Aceraceae",'espece'=>"Acer",'sous_espece'=>"saccharinum"],
            ['groupe'=>1,'nom'=>"Érable champêtre",'famille'=>"Aceraceae",'espece'=>"Acer",'sous_espece'=>"campestre"],
            ['groupe'=>1,'nom'=>"Érable à feuille de frênes",'famille'=>"Aceraceae",'espece'=>"Acer",'sous_espece'=>"negundo"],

            // ====== GROUPE 2  ======
            ['groupe'=>2,'nom'=>"Catalpa",'famille'=>"Bignoniaceae",'espece'=>"Catalpa",'sous_espece'=>"bignonioïdes"],
            ['groupe'=>2,'nom'=>"Arbre aux 40 écus",'famille'=>"Ginkgoaceae",'espece'=>"Ginkgo",'sous_espece'=>"biloba"],
            ['groupe'=>2,'nom'=>"Noyer noir d'Amérique",'famille'=>"Juglandaceae",'espece'=>"Juglans",'sous_espece'=>"nigra"],
            ['groupe'=>2,'nom'=>"Noyer commun",'famille'=>"Juglandaceae",'espece'=>"Juglans",'sous_espece'=>"regia"],
            ['groupe'=>2,'nom'=>"Sorbier des oiseleurs",'famille'=>"Rosaceae",'espece'=>"Sorbus",'sous_espece'=>"aucuparia"],
            ['groupe'=>2,'nom'=>"Peuplier tremble",'famille'=>"Salicaceae",'espece'=>"Populus",'sous_espece'=>"tremula"],
            ['groupe'=>2,'nom'=>"Peuplier blanc",'famille'=>"Salicaceae",'espece'=>"Populus",'sous_espece'=>"alba"],
            ['groupe'=>2,'nom'=>"Saule blanc",'famille'=>"Salicaceae",'espece'=>"Salix",'sous_espece'=>"alba"],
            ['groupe'=>2,'nom'=>"Sumac de Virginie (Vinaigrier)",'famille'=>"Anacardiaceae",'espece'=>"Rhus",'sous_espece'=>"typhina"],
            ['groupe'=>2,'nom'=>"Robinier Faux Acacia",'famille'=>"Fabaceae",'espece'=>"Robinia",'sous_espece'=>"pseudoacacia"],

            // ====== GROUPE 3  ======
            ['groupe'=>3,'nom'=>"Copalme d'Amérique",'famille'=>"Hamamelidaceae",'espece'=>"Liquidambar",'sous_espece'=>"styraciflua"],
            ['groupe'=>3,'nom'=>"Platane commun",'famille'=>"Platanaceae",'espece'=>"Platanus",'sous_espece'=>"x hispanica"],
            ['groupe'=>3,'nom'=>"Frêne commun",'famille'=>"Oléacées",'espece'=>"Fraxinus",'sous_espece'=>"excelsior"],
            ['groupe'=>3,'nom'=>"Bouleau verruqueux (blanc)",'famille'=>"Betulaceae",'espece'=>"Betula",'sous_espece'=>"verrucosa"],
            ['groupe'=>3,'nom'=>"Tilleul argenté",'famille'=>"Tiliaceae",'espece'=>"Tilia",'sous_espece'=>"tomentosa"],
            ['groupe'=>3,'nom'=>"Tilleul à grandes feuilles",'famille'=>"Tiliacées",'espece'=>"Tilia",'sous_espece'=>"platyphyllos"],
            ['groupe'=>3,'nom'=>"Aulne glutineux",'famille'=>"Betulaceae",'espece'=>"Alnus",'sous_espece'=>"glutinosa"],
            ['groupe'=>3,'nom'=>"Marronnier d'Indes",'famille'=>"Hippocastanaceae",'espece'=>"Aesculus",'sous_espece'=>"hippocastanum"],
            ['groupe'=>3,'nom'=>"Tulipier de Virginie",'famille'=>"Magnoliaceae",'espece'=>"Liriodendron",'sous_espece'=>"tulipifera"],
            ['groupe'=>3,'nom'=>"Paulownia",'famille'=>"Scrophulariaceae",'espece'=>"Paulownia",'sous_espece'=>"tomentosa"],
            // ====== GROUPE 4 - Les conifères  ======
            ['groupe'=>4,'nom'=>"Pin Sylvestre",'famille'=>"Pinacées",'espece'=>"Pinus",'sous_espece'=>"sylvestris"],
            ['groupe'=>4,'nom'=>"Pin de montagne",'famille'=>"Pinacées",'espece'=>"Pinus",'sous_espece'=>"mugo"],
            ['groupe'=>4,'nom'=>"Pin pleureur de l'Himalaya",'famille'=>"Pinacées",'espece'=>"Pinus",'sous_espece'=>"wallichiana"],
            ['groupe'=>4,'nom'=>"Sapin de Nordmann",'famille'=>"Pinacées",'espece'=>"Abies",'sous_espece'=>"nordmanniana"],
            ['groupe'=>4,'nom'=>"Sapin de Corée",'famille'=>"Pinacées",'espece'=>"Abies",'sous_espece'=>"koreana"],
            ['groupe'=>4,'nom'=>"Épicea Commun",'famille'=>"Pinacées",'espece'=>"Picea",'sous_espece'=>"abies"],
            ['groupe'=>4,'nom'=>"Épicea de Serbie",'famille'=>"Pinacées",'espece'=>"Picea",'sous_espece'=>"omorika"],
            ['groupe'=>4,'nom'=>"Cèdre de l'Atlas",'famille'=>"Pinacées",'espece'=>"Cedrus",'sous_espece'=>"atlantica"],
            ['groupe'=>4,'nom'=>"Thuya du Canada",'famille'=>"Cupressacées",'espece'=>"Thuja",'sous_espece'=>"occidentalis"],
            ['groupe'=>4,'nom'=>"Thuya géant",'famille'=>"Cupressacées",'espece'=>"Thuja",'sous_espece'=>"plicata"],
        ];
    }

    private function getOptions(array $plantesData)
    {
        $familles = array_values(array_unique(array_map(fn ($p) => $p['famille'], $plantesData)));
        $especes = array_values(array_unique(array_map(fn ($p) => $p['espece'], $plantesData)));
        $sousEspeces = array_values(array_unique(array_map(fn ($p) => $p['sous_espece'], $plantesData)));
        $noms = array_values(array_unique(array_map(fn ($p) => $p['nom'], $plantesData)));

        natcasesort($familles);
        natcasesort($especes);
        natcasesort($sousEspeces);
        natcasesort($noms);

        return [
            'familles' => array_values($familles),
            'especes' => array_values($especes),
            'sousEspeces' => array_values($sousEspeces),
            'noms' => array_values($noms),
        ];
    }

    // Normalisation pour comparaison tolérante
    private function normalize($str)
    {
        $str = trim((string)$str);
        $str = mb_strtolower($str, 'UTF-8');
        $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str); // enlève les accents
        return preg_replace('/\s+/', '', $str); // enlève espaces
    }

    public function index(Request $request)
    {
        $mode = $request->query('mode', 'simple');           // simple | difficile
        $direction = $request->query('direction', 'normal');  // normal | inverse
        $pack = $request->query('pack', 'all');               // all | g1 | g2

        // 1) Charger toutes les plantes
        $all = $this->getPlantesData();

        // 2) Filtrer selon le pack
        $filtered = array_values(array_filter($all, function($p) use ($pack) {
            if ($pack === 'all') return true;
            if ($pack === 'g1') return $p['groupe'] === 1;
            if ($pack === 'g2') return $p['groupe'] === 2;
            if ($pack === 'g3') return $p['groupe'] === 3;
            if ($pack === 'g4') return $p['groupe'] === 4;
            return true;
        }));

        // Sécurité : si filtrage vide, retomber sur all
        if (empty($filtered)) $filtered = $all;

        // 3) Options construites depuis l'ensemble filtré
        $options = $this->getOptions($filtered);

        // 4) Progression sans répétition — par pack + direction
        $sessionKey = 'plantes_vues_'.$pack.'_'.$direction;
        $plantesVues = session($sessionKey, []);

        $restantes = array_values(array_filter($filtered, fn($p) => !in_array($p['nom'], $plantesVues)));

        if (empty($restantes)) {
            $plantesVues = [];
            $restantes = $filtered;
            session()->flash('reset', true);
        }

        $planteChoisie = $restantes[array_rand($restantes)];
        $plantesVues[] = $planteChoisie['nom'];
        session([$sessionKey => $plantesVues]);

        // 5) Construire question / solutions selon direction
        if ($direction === 'normal') {
            $questionLabel = $planteChoisie['nom'];
            $solution = [
                'famille' => $planteChoisie['famille'],
                'espece' => $planteChoisie['espece'],
                'sous_espece' => $planteChoisie['sous_espece'],
                'nom' => null,
            ];
        } else {
            $questionLabel = $planteChoisie['famille'].' / '.$planteChoisie['espece'].' '.$planteChoisie['sous_espece'];
            $solution = [
                'famille' => null,
                'espece' => null,
                'sous_espece' => null,
                'nom' => $planteChoisie['nom'],
            ];
        }

        return view('revision', [
            'mode' => $mode,
            'direction' => $direction,
            'pack' => $pack,

            'question' => $questionLabel,

            'solution_famille' => $solution['famille'],
            'solution_espece' => $solution['espece'],
            'solution_sous_espece' => $solution['sous_espece'],
            'solution_nom' => $solution['nom'],

            'familles' => $options['familles'],
            'especes' => $options['especes'],
            'sousEspeces' => $options['sousEspeces'],
            'noms' => $options['noms'],

            'choix_famille' => null,
            'choix_espece' => null,
            'choix_sous_espece' => null,
            'choix_nom' => null,

            'verdict' => null,
            'etat_famille' => null,
            'etat_espece' => null,
            'etat_sous_espece' => null,
            'etat_nom' => null,

            'progression' => count($plantesVues),
            'total' => count($filtered),
        ]);
    }

    public function check(Request $request)
    {
        $mode = $request->input('mode', 'simple');
        $direction = $request->input('direction', 'normal');
        $pack = $request->input('pack', 'all');

        // réponses utilisateur
        $choix_famille = $request->input('famille');
        $choix_espece = $request->input('espece');
        $choix_sous_espece = $request->input('sous_espece');
        $choix_nom = $request->input('nom_courant');

        // solutions officielles
        $solution_famille = $request->input('solution_famille');
        $solution_espece = $request->input('solution_espece');
        $solution_sous_espece = $request->input('solution_sous_espece');
        $solution_nom = $request->input('solution_nom');

        // l'"énoncé" affiché à l'écran
        $question = $request->input('question');

        // Normalisation (case, accents, espaces)
        $normalize = function ($str) {
            $str = trim((string)$str);
            $str = mb_strtolower($str, 'UTF-8');
            $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
            return preg_replace('/\s+/', '', $str);
        };

        // Compare et retourne "ok" | "presque" | "faux"
        $compareTolerance = function ($a, $b) use ($normalize) {
            if ($a === null || $b === null) return null; // non applicable
            $a = $normalize($a);
            $b = $normalize($b);
            if ($a === $b) return 'ok';
            $distance = levenshtein($a, $b);
            return ($distance <= 4) ? 'presque' : 'faux';
        };

        // Statuts par partie (selon la direction)
        if ($direction === 'normal') {
            $etat_famille = $compareTolerance($choix_famille, $solution_famille);
            $etat_espece = $compareTolerance($choix_espece, $solution_espece);
            $etat_sous_espece = $compareTolerance($choix_sous_espece, $solution_sous_espece);
            $etat_nom = null; // non utilisé ici
            $toutBon = in_array($etat_famille, ['ok'], true)
                    && in_array($etat_espece, ['ok'], true)
                    && in_array($etat_sous_espece, ['ok'], true)
                    && !in_array('faux', [$etat_famille,$etat_espece,$etat_sous_espece], true);
        } else {
            // inverse : on évalue le nom courant
            $etat_nom = $compareTolerance($choix_nom, $solution_nom);
            $etat_famille = $etat_espece = $etat_sous_espece = null;
            $toutBon = in_array($etat_nom, ['ok'], true)
                    && $etat_nom !== 'faux';
        }

        $verdict = $toutBon ? 'ok' : 'ko';

        // IMPORTANT : options basées sur le pack sélectionné
        $all = $this->getPlantesData();
        $filtered = array_values(array_filter($all, function($p) use ($pack) {
            if ($pack === 'all') return true;
            if ($pack === 'g1') return $p['groupe'] === 1;
            if ($pack === 'g2') return $p['groupe'] === 2;
            if ($pack === 'g3') return $p['groupe'] === 3;
            if ($pack === 'g4') return $p['groupe'] === 4;
            return true;
        }));
        $options = $this->getOptions($filtered);

        return view('revision', [
            'mode' => $mode,
            'direction' => $direction,
            'pack' => $pack,

            // on réaffiche la même "question"
            'question' => $question,

            // solutions à garder dans les champs cachés
            'solution_famille' => $solution_famille,
            'solution_espece' => $solution_espece,
            'solution_sous_espece' => $solution_sous_espece,
            'solution_nom' => $solution_nom,

            // listes pour les selects
            'familles' => $options['familles'],
            'especes' => $options['especes'],
            'sousEspeces' => $options['sousEspeces'],
            'noms' => $options['noms'],

            // rejouer les choix de l'utilisateur dans les champs
            'choix_famille' => $choix_famille,
            'choix_espece' => $choix_espece,
            'choix_sous_espece' => $choix_sous_espece,
            'choix_nom' => $choix_nom,

            // verdict général
            'verdict' => $verdict,

            // feedback par champ
            'etat_famille' => $etat_famille,
            'etat_espece' => $etat_espece,
            'etat_sous_espece' => $etat_sous_espece,
            'etat_nom' => $etat_nom,
        ]);
    }
}
