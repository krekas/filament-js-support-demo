<?php

namespace App\Support;

/**
 * @phpstan-type Seat array{number: int, x: int|float, y: int|float}
 * @phpstan-type Row array{label: string, seats: list<Seat>}
 * @phpstan-type Section array{key: string, name: string, tier: string, rows: list<Row>}
 * @phpstan-type Layout array{width: int, height: int, stage: array{x: int, y: int, width: int, height: int, label: string}, sections: list<Section>}
 */
class VenueLayout
{
    /**
     * Section metadata keyed by seat key, built lazily.
     *
     * @var array<string, Section>|null
     */
    protected ?array $sectionsBySeatKey = null;

    /**
     * @param  Layout|array<string, mixed>  $layout
     */
    public function __construct(public readonly array $layout) {}

    /**
     * @param  Layout|array<string, mixed>|null  $layout
     */
    public static function make(?array $layout): self
    {
        return new self($layout ?? []);
    }

    public static function seatKey(string $section, string $row, int $number): string
    {
        return "{$section}-{$row}-{$number}";
    }

    /**
     * @return list<string>
     */
    public function seatKeys(): array
    {
        return array_keys($this->sectionsBySeatKey());
    }

    public function capacity(): int
    {
        return count($this->sectionsBySeatKey());
    }

    public function hasSeat(string $seatKey): bool
    {
        return array_key_exists($seatKey, $this->sectionsBySeatKey());
    }

    public function tierFor(string $seatKey): ?string
    {
        return $this->sectionFor($seatKey)['tier'] ?? null;
    }

    /**
     * @return Section|null
     */
    public function sectionFor(string $seatKey): ?array
    {
        return $this->sectionsBySeatKey()[$seatKey] ?? null;
    }

    /**
     * @return list<Section>
     */
    public function sections(): array
    {
        return $this->layout['sections'] ?? [];
    }

    /**
     * @return array<string, Section>
     */
    protected function sectionsBySeatKey(): array
    {
        if ($this->sectionsBySeatKey !== null) {
            return $this->sectionsBySeatKey;
        }

        $this->sectionsBySeatKey = [];

        foreach ($this->sections() as $section) {
            foreach ($section['rows'] as $row) {
                foreach ($row['seats'] as $seat) {
                    $this->sectionsBySeatKey[self::seatKey($section['key'], $row['label'], $seat['number'])] = $section;
                }
            }
        }

        return $this->sectionsBySeatKey;
    }
}
