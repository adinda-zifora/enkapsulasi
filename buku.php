<?php
declare(strict_types=1);

class Buku {
    private string $kodeBuku;
    private string $judul;
    private string $penulis;
    private int $tahun;
    private int $stok;

    public function __construct(string $kodeBuku, string $judul, string $penulis, int $tahun, int $stok) {
        $this->kodeBuku = trim($kodeBuku);
        $this->setJudul($judul);
        $this->penulis = trim($penulis);
        $this->tahun = $tahun;
        
        if ($stok < 0) {
            throw new Exception("Stok tidak boleh negatif!");
        }
        $this->stok = $stok;
    }

    public function getKodeBuku(): string {
        return $this->kodeBuku;
    }

    public function getJudul(): string {
        return $this->judul;
    }

    public function setJudul(string $judul): void {
        $judulTrim = trim($judul);
        if ($judulTrim === "") {
            throw new Exception("Judul buku wajib diisi!");
        }
        $this->judul = $judulTrim;
    }

    public function getPenulis(): string {
        return $this->penulis;
    }

    public function getTahun(): int {
        return $this->tahun;
    }

    public function getStok(): int {
        return $this->stok;
    }

    public function tambahStok(int $jumlah): void {
        $this->stok += $jumlah;
    }

    public function kurangiStok(int $jumlah): void {
        if ($this->stok - $jumlah < 0) {
            throw new Exception("Stok tidak mencukupi!");
        }
        $this->stok -= $jumlah;
    }
}

echo "<h2>PENGUJIAN GETTER, SETTER, DAN VALIDASI</h2>";

// 1. Input Valid 
echo "<b>1. Input Valid</b><br>";
try {
    $buku1 = new Buku("BK001", "Pemrograman PHP", "Adinda", 2023, 10);
    echo "Kode Buku: " . $buku1->getKodeBuku() . "<br>";
    echo "Judul Buku: " . $buku1->getJudul() . "<br>";
    echo "Penulis: " . $buku1->getPenulis() . "<br>";
    echo "Tahun: " . $buku1->getTahun() . "<br>";
    echo "Stok Buku: " . $buku1->getStok() . "<br><br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br><br>";
}

// 2. Input Valid 
echo "<b>2. Input Valid - Tambah Stok</b><br>";
try {
    $buku2 = new Buku("BK002", "Belajar PHP", "Dinda", 2024, 10);
    $buku2->tambahStok(5);
    echo "Stok berhasil ditambahkan.<br>";
    echo "Stok sekarang: " . $buku2->getStok() . "<br>";
    
    $buku2->setJudul("Belajar PHP Lanjutan");
    echo "Judul berhasil diubah.<br>";
    echo "Judul sekarang: " . $buku2->getJudul() . "<br><br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br><br>";
}

// 3. Input Tidak Valid 
echo "<b>3. Input Tidak Valid - Judul Kosong</b><br>";
try {
    $buku3 = new Buku("BK003", "", "Zifor", 2022, 10);
} catch (Exception $e) {
    echo "Judul buku wajib diisi!<br><br>";
}

// 4. Input Tidak Valid - Stok NegatiF
echo "<b>4. Input Tidak Valid - Stok Negatif</b><br>";
try {
    $buku4 = new Buku("BK004", "Struktur Data", "Zifor", 2022, -5);
} catch (Exception $e) {
    echo "Stok tidak boleh negatif!<br><br>";
}

// 5. Input Tidak Valid - Stok Tidak Mencukupi
echo "<b>5. Input Tidak Valid - Stok Tidak Mencukupi</b><br>";
try {
    $buku5 = new Buku("BK005", "Struktur Data", "Zifor", 2022, 5);
    $buku5->kurangiStok(10);
} catch (Exception $e) {
    echo "Stok tidak mencukupi!<br>";
}
?>