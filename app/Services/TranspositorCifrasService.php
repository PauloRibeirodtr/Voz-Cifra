<?php

namespace App\Services;

class TranspositorCifrasService
{
    private const NOTE_TO_SEMITONE = [
        'C' => 0,
        'B#' => 0,
        'C#' => 1,
        'Db' => 1,
        'D' => 2,
        'D#' => 3,
        'Eb' => 3,
        'E' => 4,
        'Fb' => 4,
        'E#' => 5,
        'F' => 5,
        'F#' => 6,
        'Gb' => 6,
        'G' => 7,
        'G#' => 8,
        'Ab' => 8,
        'A' => 9,
        'A#' => 10,
        'Bb' => 10,
        'B' => 11,
        'Cb' => 11,
    ];

    private const SEMITONE_TO_SHARP = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];

    private const SEMITONE_TO_FLAT = ['C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B'];

    public function calcularPassos(?string $tomOriginal, ?string $tomDestino): int
    {
        if (!$this->ehAcorde($tomOriginal) || !$this->ehAcorde($tomDestino)) {
            return 0;
        }

        $original = $this->parseChord($tomOriginal);
        $destino = $this->parseChord($tomDestino);

        if (!$original || !$destino) {
            return 0;
        }

        $semitomOriginal = $this->getSemitone($original['root']);
        $semitomDestino = $this->getSemitone($destino['root']);

        if ($semitomOriginal === null || $semitomDestino === null) {
            return 0;
        }

        return $semitomDestino - $semitomOriginal;
    }

    public function transporTextoCifrado(?string $texto, int $passos): string
    {
        $texto = $this->normalizeWhitespace($texto);

        if ($passos === 0 || $texto === '') {
            return $texto;
        }

        return preg_replace_callback('/\[([^\[\]\r\n]+)\]/', function (array $matches) use ($passos): string {
            $acorde = trim((string) ($matches[1] ?? ''));

            return $this->ehAcorde($acorde)
                ? '[' . $this->transposeChord($acorde, $passos) . ']'
                : $matches[0];
        }, $texto) ?? $texto;
    }

    public function transporTomExibicao(?string $tomOriginal, ?string $tomDestino): ?string
    {
        if (!$this->ehAcorde($tomOriginal)) {
            return $tomDestino ?: $tomOriginal;
        }

        if (!$this->ehAcorde($tomDestino)) {
            return $tomOriginal;
        }

        return $tomDestino;
    }

    private function transposeChord(string $chord, int $steps): string
    {
        $parsed = $this->parseChord($chord);

        if (!$parsed || $steps === 0) {
            return $chord;
        }

        $rootPrefersFlats = str_contains($parsed['root'], 'b');
        $bassPrefersFlats = $parsed['bass'] ? str_contains($parsed['bass'], 'b') : false;

        return implode('', [
            $this->transposeNote($parsed['root'], $steps, $rootPrefersFlats),
            $parsed['suffix'],
            $parsed['bass'] ? '/' . $this->transposeNote($parsed['bass'], $steps, $bassPrefersFlats) : '',
        ]);
    }

    private function transposeNote(string $note, int $steps, bool $preferFlats = false): string
    {
        $semitone = $this->getSemitone($note);

        if ($semitone === null) {
            return $note;
        }

        $scale = $preferFlats ? self::SEMITONE_TO_FLAT : self::SEMITONE_TO_SHARP;

        return $scale[(($semitone + $steps) % 12 + 12) % 12];
    }

    private function parseChord(?string $value): ?array
    {
        $chord = trim((string) $value);

        if (!$this->ehAcorde($chord)) {
            return null;
        }

        if (preg_match('/^([A-G](?:#|b)?)(.*?)(?:\/([A-G](?:#|b)?))?$/', $chord, $matches) !== 1) {
            return null;
        }

        return [
            'root' => $matches[1],
            'suffix' => $matches[2] ?? '',
            'bass' => $matches[3] ?? null,
        ];
    }

    private function ehAcorde(?string $value): bool
    {
        $chord = trim((string) $value);

        if ($chord === '' || str_contains($chord, ' ')) {
            return false;
        }

        return preg_match('/^[A-G](?:#|b)?(?:(?:maj|min|dim|aug|sus|add|omit|no|m|M|º|°|\+|-|[0-9#b])|\([^)\]]+\))*(?:\/[A-G](?:#|b)?)?$/', $chord) === 1;
    }

    private function getSemitone(string $note): ?int
    {
        return self::NOTE_TO_SEMITONE[$note] ?? null;
    }

    private function normalizeWhitespace(?string $value): string
    {
        return str_replace(["\r\n", "\r"], "\n", trim((string) $value));
    }
}
