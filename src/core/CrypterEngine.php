<?php
class CrypterEngine {
    private $code;
    private $lang;
    private $pipeline;
    private $originalSize = 0;

    public function __construct($code, $lang = 'auto', $filename = '') {
        $this->code = $code;
        $this->originalSize = strlen($code);
        $this->lang = ($lang === 'auto') ? detectLanguage($code, $filename) : $lang;
    }

    public function setPipeline($steps) { $this->pipeline = $steps; return $this; }

    public function setLevel($level) {
        $profile = ProtectionProfile::get(min(max($level, 1), 12));
        $this->pipeline = $profile['pipeline'] ?? [];
        return $this;
    }

    public function execute() {
        $builder = new PipelineBuilder($this->code, $this->lang);
        $builder->setSteps($this->pipeline);
        $result = $builder->execute();
        $result['language'] = $this->lang;
        $result['original_size'] = $this->originalSize;
        $result['expansion_ratio'] = $this->originalSize > 0 ? round($result['final_size'] / $this->originalSize, 2) : 1;
        $result['entropy'] = $this->calculateEntropy($result['code']);
        return $result;
    }

    private function calculateEntropy($data) {
        if (strlen($data) === 0) return 0;
        $freq = count_chars($data, 1);
        $len = strlen($data);
        $entropy = 0;
        foreach ($freq as $count) { $p = $count / $len; $entropy -= $p * log($p, 256); }
        return round($entropy, 4);
    }
}
