<?php
declare(strict_types=1);

class Mahasiswa {
    private string $nim;
    private string $nama;
    private string $fakultas;
    private string $prodi;
    private string $asalSekolah;
    private int $semester;
    private string $hobi;
    private string $agama;

    private const DAFTAR_AGAMA = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];

    public function __construct(
        string $nim,
        string $nama,
        string $fakultas,
        string $prodi,
        string $asalSekolah,
        int $semester,
        string $hobi,
        string $agama
    ) {
        $nimTrim = trim($nim);
        if ($nimTrim === '') {
            throw new InvalidArgumentException('NIM wajib diisi.');
        }
        $this->nim = $nimTrim;

        $this->setNama($nama);
        $this->setFakultas($fakultas);
        $this->setProdi($prodi);
        $this->setAsalSekolah($asalSekolah);
        $this->setSemester($semester);
        $this->setHobi($hobi);
        $this->setAgama($agama);
    }

    // Getter
    public function getNim(): string { return $this->nim; }
    public function getNama(): string { return $this->nama; }
    public function getFakultas(): string { return $this->fakultas; }
    public function getProdi(): string { return $this->prodi; }
    public function getAsalSekolah(): string { return $this->asalSekolah; }
    public function getSemester(): int { return $this->semester; }
    public function getHobi(): string { return $this->hobi; }
    public function getAgama(): string { return $this->agama; }

    // Setter & Validasi
    public function setNama(string $nama): void {
        $namaTrim = trim($nama);
        if ($namaTrim === '') {
            throw new InvalidArgumentException('Nama tidak boleh kosong.');
        }
        $this->nama = $namaTrim;
    }

    public function setFakultas(string $fakultas): void {
        $this->fakultas = trim($fakultas) !== '' ? trim($fakultas) : 'Belum diisi';
    }

    public function setProdi(string $prodi): void {
        $this->prodi = trim($prodi);
    }

    public function setAsalSekolah(string $asalSekolah): void {
        $this->asalSekolah = trim($asalSekolah);
    }

    public function setSemester(int $semester): void {
        if ($semester < 1 || $semester > 14) {
            throw new InvalidArgumentException('Semester harus 1 sampai 14.');
        }
        $this->semester = $semester;
    }

    public function setHobi(string $hobi): void {
        $this->hobi = trim($hobi);
    }

    public function setAgama(string $agama): void {
        $agamaTrim = trim($agama);
        if (!in_array($agamaTrim, self::DAFTAR_AGAMA, true)) {
            throw new InvalidArgumentException('Pilihan agama tidak valid.');
        }
        $this->agama = $agamaTrim;
    }
}
?>