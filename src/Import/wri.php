<?php

namespace Kabano\Import;

use Kabano\Poi;
use Exception;

require_once($config['models_folder']."d.poi.php");
require_once($config['includes_folder']."poi_types.struct.php");

class WriFetcher
{
    private string $url;

    public function __construct(
        string $url = 'https://www.refuges.info/api/bbox?detail=complet&nb_points=all'
    ) {
        $this->url = $url;
    }

    public function fetchAll(): array
    {
        $json = $this->download($this->url);

        $data = json_decode($json, true);

        if ($data === null) {
            throw new Exception(
                'JSON invalide reçu depuis refuges.info : ' . json_last_error_msg()
            );
        }

        return $data;
    }

    private function download(string $url): string
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'KabanoBot/1.0 (+https://kabano.org)',
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            throw new Exception("Erreur réseau lors du téléchargement : $error");
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($status < 200 || $status >= 300) {
            throw new Exception("HTTP $status reçu depuis $url");
        }

        return $response;
    }
}

class WriMapper
{
    private array $poi_types;

    public function __construct()
    {
        global $poi_types;
        $this->poi_types = $poi_types;
    }

    public function map(array $raw): Poi
    {
        if (!isset($raw['properties']) || !isset($raw['geometry'])) {
            throw new Exception("Feature GeoJSON invalide");
        }

        $p = $raw['properties'];
        $g = $raw['geometry'];

        // Type WRI (structure confirmée)
        $wriTypeId = $p['type']['id'] ?? null;

        // Filtrage strict
        $poiType = $this->mapTypeFromWriId((int)$wriTypeId);
        if ($poiType === null) {
            throw new Exception("Type WRI non supporté : $wriTypeId");
        }
        // Si WRI indique "manque un mur", on force le type en basic_hut
        if (($p['info_comp']['manque_un_mur']['valeur'] ?? '') === 'Oui') {
            $poiType = 'basic_hut';
        }

        $poi = new Poi();

        $poi->source_id = 'wri';
        $poi->remote_source_id = $p['id'];

        $poi->name = $p['nom'] ?? '—';
        $poi->permalink = $this->slugify($poi->name);

        $poi->poi_type = $poiType;

        // Coordonnées
        $poi->lat = $g['coordinates'][1] ?? null;
        $poi->lon = $g['coordinates'][0] ?? null;
        $poi->ele = $p['coord']['alt'] ?? 0;

        $poi->locale = 'fr_FR';
        $poi->is_commentable = true;

        // Paramètres JSON conformes à ton modèle
        $poi->parameters = json_encode(
            $this->buildParameters($poiType, $p),
            JSON_UNESCAPED_UNICODE
        );

        return $poi;
    }

    private function mapTypeFromWriId(int $id): ?string
    {
        return match ($id) {
            10 => 'alpine_hut',      // refuge gardé
            7  => 'wilderness_hut',  // cabane non gardée
            9  => 'halt',            // gîte d'étape
            default => null
        };
    }

    private function cleanValue($value): string
    {
        if (!is_string($value)) return '';
        $value = strip_tags(str_replace(['[b]', '[/b]'], '', $value));
        return trim($value);
    }

    private function isYes($value): int
    {
        // Nettoyage des balises WRI
        $clean = strip_tags(str_replace(['[b]', '[/b]'], '', (string)$value));
        $clean = trim($clean);

        return match ($clean) {
            'Oui' => 2,
            'Non' => 0,
            'Inconnu' => 1,
            default => -1
        };
    }

    private function buildParameters(string $poi_type, array $p): array
    {
        $fields = $this->poi_types[$poi_type][5];
        $params = [];

        foreach ($fields as $key => $label) {

            switch (true) {

                // TEXTES
                case str_starts_with($key, 't_'):
                    $params[$key] = match ($key) {
                        't_owner'       => $p['proprio']['valeur'] ?? '',
                        't_access'      => $p['acces']['valeur'] ?? '',
                        't_description' => $p['remarque']['valeur'] ?? '',
                        default         => '',
                    };
                    break;

                // BOOLÉENS
                case str_starts_with($key, 'b_'):
                    $params[$key] = $this->guessBoolean($key, $p);
                    break;

                // NUMÉRIQUES
                case str_starts_with($key, 'n_'):
                    $params[$key] = $this->guessNumeric($key, $p);
                    break;

                // LIENS
                case str_starts_with($key, 'l_'):
                    $params[$key] = null; // Non géré par WRI
                    break;

                default:
                    $params[$key] = "";
            }
        }

        return $params;
    }

    private function guessBoolean(string $key, array $p): int
    {
        $etatId = $p['etat']['id'] ?? '';

        return match ($key) {
            'b_usable' =>
                in_array($etatId, ['detruit', 'fermeture'], true) ? 2 : 0,
            'b_water'     => $this->isYes($p['info_comp']['eau']['valeur'] ?? ''),
            'b_wood'      => $this->isYes($p['info_comp']['bois']['valeur'] ?? ''),
            'b_cover'     => $this->isYes($p['info_comp']['couvertures']['valeur'] ?? ''),
            'b_toilet'    => $this->isYes($p['info_comp']['latrines']['valeur'] ?? ''),
            'b_fireplace' => max(
                $this->isYes($p['info_comp']['cheminee']['valeur'] ?? ''),
                $this->isYes($p['info_comp']['poele']['valeur'] ?? '')
            ),
            'b_key' =>
                $etatId === 'cle_a_recuperer' ? 2 : 0,
            default => -1,
        };
    }

    private function guessNumeric(string $key, array $p): ?int
    {
        $raw = match ($key) {
            'n_bed'        => $p['places']['valeur'] ?? null,
            'n_mattress'   => $p['info_comp']['places_matelas']['valeur'] ?? null,
            'n_bed_winter' => null, // WRI ne fournit pas
            default        => null,
        };

        if (!is_numeric($raw)) {
            return null;
        }

        return (int)$raw;
    }

    private function slugify(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('/[^a-zA-Z0-9]+/', '-', $text);
        return strtolower(trim($text, '-'));
    }
}

class WriImporter
{
    private WriFetcher $fetcher;
    private WriMapper $mapper;

    public function __construct()
    {
        $this->fetcher = new WriFetcher();
        $this->mapper = new WriMapper();
    }

    public function importAll(bool $dryRun = false): array
    {
        $geojson = $this->fetcher->fetchAll();

        if (!isset($geojson['features']) || !is_array($geojson['features'])) {
            throw new Exception("Format GeoJSON inattendu");
        }

        $raws = $geojson['features'];

        $created = 0;
        $updated = 0;

        foreach ($raws as $raw) {

            try {
                $poi = $this->mapper->map($raw);
            } catch (\Exception $e) {
                continue; // Type non supporté
            }

            // Vérifier si un POI existe déjà
            $existing = new Poi();
            if ($existing->checkPermalink($poi->permalink, 1)) {

                if (!$dryRun) {
                    $existing->lat = $poi->lat;
                    $existing->lon = $poi->lon;
                    $existing->ele = $poi->ele;
                    $existing->parameters = $poi->parameters;
                    $existing->poi_type = $poi->poi_type;
                    $existing->source_id = 'wri';
                    $existing->remote_source_id = $poi->remote_source_id;
                    $existing->update();
                }

                $updated++;
            } else {

                if (!$dryRun) {
                    $poi->insert();
                }

                $created++;
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'total'   => count($raws),
        ];
    }
}
