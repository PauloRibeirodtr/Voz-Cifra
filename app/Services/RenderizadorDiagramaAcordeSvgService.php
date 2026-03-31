<?php

namespace App\Services;

class RenderizadorDiagramaAcordeSvgService
{
    public function renderizar(?array $shape): ?string
    {
        if (!is_array($shape) || $shape === []) {
            return null;
        }

        $config = [
            'startX' => 30,
            'startY' => 40,
            'width' => 180,
            'height' => 240,
            'numStrings' => 6,
            'numFrets' => 5,
        ];

        $stringGap = $config['width'] / ($config['numStrings'] - 1);
        $fretGap = $config['height'] / $config['numFrets'];
        $baseFret = max(1, (int) ($shape['baseFret'] ?? 1));
        $positions = is_array($shape['positions'] ?? null) ? $shape['positions'] : [];
        $barres = is_array($shape['barres'] ?? null) ? $shape['barres'] : [];
        $topMarkers = is_array($shape['topMarkers'] ?? null) ? $shape['topMarkers'] : [null, null, null, null, null, null];

        $grid = '';
        $marks = '';

        if ($baseFret === 1) {
            $grid .= sprintf(
                '<rect x="%d" y="%d" width="%d" height="6" rx="2" fill="#111827" />',
                $config['startX'],
                $config['startY'] - 6,
                $config['width']
            );
        } else {
            $grid .= sprintf(
                '<text x="%d" y="%d" text-anchor="end" fill="#475569" font-weight="bold" font-size="18">%sa</text>',
                $config['startX'] - 10,
                $config['startY'] + 25,
                $baseFret
            );
            $grid .= sprintf(
                '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#64748b" stroke-width="2" />',
                $config['startX'],
                $config['startY'],
                $config['startX'] + $config['width'],
                $config['startY']
            );
        }

        for ($i = 1; $i <= $config['numFrets']; $i++) {
            $y = $config['startY'] + ($i * $fretGap);
            $grid .= sprintf(
                '<line x1="%d" y1="%s" x2="%d" y2="%s" stroke="#94a3b8" stroke-width="2" />',
                $config['startX'],
                $y,
                $config['startX'] + $config['width'],
                $y
            );
        }

        for ($i = 0; $i < $config['numStrings']; $i++) {
            $x = $config['startX'] + ($i * $stringGap);
            $thickness = 0.8 + ((5 - $i) * 0.5);
            $grid .= sprintf(
                '<line x1="%s" y1="%d" x2="%s" y2="%d" stroke="#cbd5e1" stroke-width="%s" />',
                $x,
                $config['startY'],
                $x,
                $config['startY'] + $config['height'],
                $thickness
            );
        }

        foreach ($topMarkers as $i => $marker) {
            $x = $config['startX'] + ($i * $stringGap);
            $y = $config['startY'] - 15;

            if ($marker === 'muted') {
                $marks .= sprintf(
                    '<text x="%s" y="%s" fill="#dc2626" font-size="18" font-weight="900" text-anchor="middle">X</text>',
                    $x,
                    $y + 5
                );
            } elseif ($marker === 'open') {
                $marks .= sprintf(
                    '<circle cx="%s" cy="%s" r="5" stroke="#2563eb" stroke-width="2.5" fill="none" />',
                    $x,
                    $y
                );
            }
        }

        foreach ($barres as $barre) {
            $fret = (int) ($barre['fret'] ?? 0);
            $fromString = (int) ($barre['fromString'] ?? 0);
            $toString = (int) ($barre['toString'] ?? 0);

            if ($fret < 1 || $fromString < 1 || $toString < 1) {
                continue;
            }

            $y = $config['startY'] + ($fret * $fretGap) - ($fretGap / 2);
            $x1 = $config['startX'] + (($config['numStrings'] - $fromString) * $stringGap);
            $x2 = $config['startX'] + (($config['numStrings'] - $toString) * $stringGap);

            $marks .= sprintf(
                '<line x1="%s" y1="%s" x2="%s" y2="%s" stroke="#ea580c" stroke-width="14" stroke-linecap="round" opacity="0.95" />',
                $x1,
                $y,
                $x2,
                $y
            );
        }

        foreach ($positions as $position) {
            $fret = (int) ($position['fret'] ?? 0);
            $string = (int) ($position['string'] ?? 0);

            if ($fret < 1 || $string < 1) {
                continue;
            }

            $y = $config['startY'] + ($fret * $fretGap) - ($fretGap / 2);
            $x = $config['startX'] + (($config['numStrings'] - $string) * $stringGap);

            $marks .= sprintf(
                '<circle cx="%s" cy="%s" r="12" fill="#ea580c" />',
                $x,
                $y
            );

            if (!empty($position['finger'])) {
                $marks .= sprintf(
                    '<text x="%s" y="%s" fill="white" font-size="14" font-weight="800" text-anchor="middle" dominant-baseline="central">%s</text>',
                    $x,
                    $y + 1,
                    e((string) $position['finger'])
                );
            }
        }

        return sprintf(
            '<svg viewBox="0 0 240 300" aria-label="Diagrama do acorde"><rect x="30" y="40" width="180" height="240" rx="4" fill="#2e1a12" stroke="#1a0f0a" stroke-width="2"></rect>%s%s</svg>',
            $grid,
            $marks
        );
    }
}
