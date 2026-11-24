<?php

namespace App\Services;

class DiabetesScreeningService
{
    protected array $modelData;

    public function __construct()
    {
        $path = storage_path('app/ml/model_web_bundle.json');
        if (!file_exists($path)) {
            throw new \RuntimeException("Model JSON not found at {$path}");
        }
        $json = file_get_contents($path);
        $this->modelData = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON: ' . json_last_error_msg());
        }
    }

    /**
     * Return all metadata needed for the UI.
     */
    public function getMetadata(): array
    {
        return [
            'project_title'   => $this->modelData['project_title'] ?? '',
            'disclaimer'      => $this->modelData['disclaimer'] ?? '',
            'dataset_summary' => $this->modelData['dataset_summary'] ?? [],
            'all_benchmarks'  => $this->modelData['all_benchmarks'] ?? [],
            'models'          => $this->modelData['models'] ?? [],
        ];
    }

    /**
     * Run prediction using the pure-PHP Logistic Regression pipeline.
     *
     * @param string     $mode   "versi_a" (8 features) or "versi_b" (6 features)
     * @param array      $input  feature_name => value
     * @param float|null $threshold  custom decision threshold (null = use model default)
     * @return array
     */
    public function predict(string $mode, array $input, ?float $threshold = null): array
    {
        // --- Validate mode ---
        if (!in_array($mode, ['versi_a', 'versi_b'], true)) {
            throw new \InvalidArgumentException("Mode must be 'versi_a' or 'versi_b'.");
        }
        if (!isset($this->modelData['models'][$mode])) {
            throw new \RuntimeException("Model definition for '{$mode}' not found in JSON.");
        }

        $model    = $this->modelData['models'][$mode];
        $params   = $model['parameters'];
        $features = $model['features'];
        $usedThreshold = $threshold ?? (float) $model['default_threshold'];

        // --- Imputation ---
        $imputedFields = [];
        $clinicalZeroFeatures = ['Glucose', 'BloodPressure', 'SkinThickness', 'Insulin', 'BMI'];
        $values = [];

        foreach ($features as $f) {
            $val = $input[$f] ?? null;

            $needsImpute = ($val === null || $val === '' || !is_numeric($val));
            if (!$needsImpute && in_array($f, $clinicalZeroFeatures, true) && (float) $val == 0) {
                $needsImpute = true;
            }

            if ($needsImpute) {
                $val = (float) ($params['medians'][$f] ?? 0);
                $imputedFields[] = $f;
            } else {
                $val = (float) $val;
            }
            $values[$f] = $val;
        }

        // --- Scikit-Learn pipeline: StandardScaler → Logistic Regression ---
        $logit = (float) $params['intercept'];
        foreach ($features as $f) {
            $z = ($values[$f] - $params['scaler_mean'][$f]) / $params['scaler_scale'][$f];
            $logit += $params['coefficients'][$f] * $z;
        }

        // Sigmoid
        $prob = 1.0 / (1.0 + exp(-$logit));

        // Decision
        $prediction = ($prob >= $usedThreshold) ? 1 : 0;

        if ($prediction === 1) {
            $riskLabel = 'Prioritas Pemeriksaan Laboratorium Lanjut';
            $recommendation = 'Pasien memiliki probabilitas risiko diabetes yang melampaui ambang keputusan. '
                . 'Disarankan untuk segera dirujuk ke laboratorium untuk pemeriksaan glukosa darah puasa, '
                . 'HbA1c, dan evaluasi lanjutan oleh dokter.';
        } else {
            $riskLabel = 'Risiko Rendah — Pemantauan Rutin';
            $recommendation = 'Probabilitas risiko di bawah ambang keputusan saat ini. '
                . 'Lanjutkan pemantauan rutin melalui Posbindu/Puskesmas dan edukasi gaya hidup sehat '
                . '(pola makan, aktivitas fisik, kontrol berat badan).';
        }

        // Top factors from feature_importances in the model
        $featureImportances = $model['feature_importances'] ?? [];

        return [
            'probability'         => round($prob, 6),
            'probability_pct'     => round($prob * 100, 2),
            'prediction'          => $prediction,
            'risk_label'          => $riskLabel,
            'recommendation'      => $recommendation,
            'imputed_fields'      => $imputedFields,
            'used_threshold'      => $usedThreshold,
            'top_factors'         => $featureImportances,
            'input_values'        => $values,
            'mode'                => $mode,
            'model_title'         => $model['title'] ?? '',
            'disclaimer'          => $this->modelData['disclaimer'] ?? '',
        ];
    }
}
