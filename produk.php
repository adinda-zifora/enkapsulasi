<?php
declare(strict_types=1);

// Class Induk: Produk
class Produk {
    public string $nama;
    protected string $kategori;
    private float $harga;

    public function __construct(string $nama, string $kategori, float $harga) {
        $this->nama = trim($nama);
        $this->kategori = trim($kategori);
        
        if ($harga < 0) {
            throw new InvalidArgumentException("Harga produk tidak boleh negatif!");
        }
        $this->harga = $harga;
    }

    public function getHarga(): float {
        return $this->harga;
    }

    public function getKategori(): string {
        return $this->kategori;
    }
}

// Subclass: ProdukDigital
class ProdukDigital extends Produk {
    public function getKategoriProduk(): string {
        return $this->kategori; 
    }
}

// --- OUTPUT SESUAI FORMAT LAPORAN ---
echo "<h2>Pengujian Class Produk</h2>";

$produk = new Produk("Laptop ASUS Vivobook", "Elektronik", 15000000);

echo "Nama Produk: " . $produk->nama . "<br>";
echo "Akses kategori dari luar: Ditolak karena protected<br>";
echo "Akses harga dari luar: Ditolak karena private<br><br>";

echo "Nama Produk: " . $produk->nama . "<br>";
echo "Kategori: " . $produk->getKategori() . "<br>";
echo "Harga: Rp " . number_format($produk->getHarga(), 0, ',', '.') . "<br><br>";

echo "<h2>Pengujian Class ProdukDigital</h2>";
$digital = new ProdukDigital("E-Book Modul PBO PHP", "Edukasi", 75000);

echo "Nama Produk: " . $digital->nama . "<br>";
echo "Kategori dari subclass: " . $digital->getKategoriProduk() . "<br>";
?>