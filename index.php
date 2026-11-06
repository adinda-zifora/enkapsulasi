<?php
declare(strict_types=1);
// Class harus dimuat sebelum session_start agar object session bisa dipulihkan.
require_once __DIR__ . '/Mahasiswa.php';
session_start();

function aman(mixed $teks): string {
    return htmlspecialchars((string)$teks, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Inisialisasi session data mahasiswa jika belum ada
if (!isset($_SESSION['mahasiswa']) || !is_array($_SESSION['mahasiswa'])) {
    $_SESSION['mahasiswa'] = [];
}

$pesan = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$error = '';
$agamaList = ['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'];
$keys = [
    'nim'=>'NIM','nama'=>'Nama','fakultas'=>'Fakultas',
    'prodi'=>'Program Studi','asalSekolah'=>'Asal Sekolah',
    'semester'=>'Semester','hobi'=>'Hobi','agama'=>'Agama'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
            throw new InvalidArgumentException('Token formulir tidak valid. Muat ulang halaman.');
        }
        $aksi = (string)($_POST['aksi'] ?? '');
        $nimLama = (string)($_POST['nim_lama'] ?? '');

        if ($aksi === 'hapus') {
            $nimTarget = trim((string)($_POST['nim'] ?? ''));
            foreach ($_SESSION['mahasiswa'] as $index => $mhs) {
                if ($mhs->getNim() === $nimTarget) {
                    unset($_SESSION['mahasiswa'][$index]);
                    $_SESSION['mahasiswa'] = array_values($_SESSION['mahasiswa']);
                    break;
                }
            }
        } elseif (in_array($aksi, ['tambah','ubah'], true)) {
            $data = [];
            foreach ($keys as $k => $label) {
                $data[$k] = trim((string)($_POST[$k] ?? ''));
            }
            foreach (['nim','nama','fakultas','prodi','asalSekolah','agama'] as $k) {
                if ($data[$k] === '') throw new InvalidArgumentException($keys[$k] . ' wajib diisi.');
            }
            if (!in_array($data['agama'], $agamaList, true)) {
                throw new InvalidArgumentException('Pilihan agama tidak valid.');
            }
            $semester = filter_var($data['semester'], FILTER_VALIDATE_INT);
            if ($semester === false || $semester < 1 || $semester > 14) {
                throw new InvalidArgumentException('Semester harus 1–14.');
            }

            // Cek keunikan NIM saat Tambah Data
            if ($aksi === 'tambah') {
                foreach ($_SESSION['mahasiswa'] as $mhsExist) {
                    if ($mhsExist->getNim() === $data['nim']) {
                        throw new InvalidArgumentException('NIM sudah terdaftar! NIM harus unik.');
                    }
                }
            }

            if ($aksi === 'tambah') {
                // Constructor dipanggil saat object dibuat (semester di-cast ke int)
                $m = new Mahasiswa(
                    $data['nim'], $data['nama'], $data['fakultas'], $data['prodi'],
                    $data['asalSekolah'], (int)$data['semester'], $data['hobi'], $data['agama']
                );
                $_SESSION['mahasiswa'][] = $m;
                $_SESSION['flash'] = 'Data mahasiswa berhasil ditambahkan.';
            } elseif ($aksi === 'ubah') {
                $ditemukan = false;
                foreach ($_SESSION['mahasiswa'] as $mhsExist) {
                    if ($mhsExist->getNim() === $nimLama) {
                        $mhsExist->setNama($data['nama']);
                        $mhsExist->setFakultas($data['fakultas']);
                        $mhsExist->setProdi($data['prodi']);
                        $mhsExist->setAsalSekolah($data['asalSekolah']);
                        $mhsExist->setSemester((int)$data['semester']);
                        $mhsExist->setHobi($data['hobi']);
                        $mhsExist->setAgama($data['agama']);
                        $ditemukan = true;
                        break;
                    }
                }
                if (!$ditemukan) {
                    throw new InvalidArgumentException('Data mahasiswa yang akan diubah tidak ditemukan.');
                }
                $_SESSION['flash'] = 'Data mahasiswa berhasil diubah.';
            }
        } else {
            throw new InvalidArgumentException('Aksi tidak dikenal.');
        }

        header('Location: index.php');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));

// Cari data berdasarkan parameter GET edit
$editData = null;
$editNim = (string)($_GET['edit'] ?? '');
if ($editNim !== '') {
    foreach ($_SESSION['mahasiswa'] as $mhs) {
        if ($mhs->getNim() === $editNim) {
            $editData = $mhs;
            break;
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Praktikum PBO - CRUD Mahasiswa</title>
<style>
    *{box-sizing:border-box}body{font-family:system-ui,Arial,sans-serif;background:#f1f5f9;color:#15324d;margin:0}
    main{max-width:1120px;margin:36px auto;padding:0 18px}h1{margin-bottom:4px}
    small,.muted{color:#60758a}.panel{background:white;border-radius:13px;padding:24px;margin:20px 0;box-shadow:0 5px 18px #16324d14}
    .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
    label{font-weight:650;font-size:13px;display:block}input,select{display:block;width:100%;padding:11px;margin-top:6px;border:1px solid #c8d5e3;border-radius:7px;background:white;font:inherit}
    input:read-only { background-color: #e9ecef; cursor: not-allowed; }
    button,.btn{border:0;border-radius:7px;background:#176397;color:white;padding:9px 15px;cursor:pointer;text-decoration:none;display:inline-block;font-size:13px}
    .danger{background:#b9414a}.actions{display:flex;gap:7px;align-items:center}.actions form{margin:0}
    table{border-collapse:collapse;width:100%;font-size:13px}th,td{padding:12px 9px;text-align:left;border-bottom:1px solid #e1e8f0}th{background:#e8f1f8}
    .scroll{overflow-x:auto}.notice{padding:12px;border-radius:8px;background:#e3f4e9}.err{background:#ffe8e8;color:#b9414a}
    @media(max-width:650px){.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<main>

<h1>Data Mahasiswa</h1><p class="muted">PBO: Constructor, Enkapsulasi, Getter/Setter + CRUD Session</p>
<?php if ($pesan): ?><div class="notice"><?= aman($pesan) ?></div><?php endif; ?>
<?php if ($error): ?><div class="notice err"><strong>Error: </strong><?= aman($error) ?></div><?php endif; ?>

<section class="panel"><h2><?= $editData ? 'Edit' : 'Tambah' ?> Data Mahasiswa</h2>

<form method="post">
<input type="hidden" name="csrf" value="<?= aman($_SESSION['csrf']) ?>">
<input type="hidden" name="aksi" value="<?= $editData ? 'ubah' : 'tambah' ?>">
<input type="hidden" name="nim_lama" value="<?= aman($editData?->getNim() ?? '') ?>">
<div class="grid">

<?php foreach ($keys as $key => $label):
    $value = '';
    if ($editData) {
        $method = 'get' . ucfirst($key);
        if (method_exists($editData, $method)) {
            $value = $editData->$method();
        }
    }
?>
<div>
<label><?= aman($label) ?></label>
<?php if ($key === 'agama'): ?>
<select name="agama" required><option value="">-- Pilih Agama --</option>
<?php foreach ($agamaList as $agama): ?>
<option value="<?= aman($agama) ?>" <?= $value === $agama ? 'selected' : '' ?>><?= aman($agama) ?></option>
<?php endforeach; ?></select>
<?php else: ?>
<input name="<?= aman($key) ?>" value="<?= aman((string)$value) ?>"
<?= $key === 'semester' ? 'type="number" min="1" max="14"' : 'type="text"' ?>
<?= ($key === 'nim' && $editData) ? 'readonly' : '' ?>
<?= $key !== 'hobi' ? 'required' : '' ?>>
<?php if ($key === 'nim' && $editData): ?>
    <small class="muted">NIM bersifat tetap dan tidak dapat diubah.</small>
<?php endif; ?>
<?php endif; ?>
</div>
<?php endforeach; ?></div>

<p style="margin-top: 20px;">
    <button><?= $editData ? 'Simpan Perubahan' : 'Tambah Mahasiswa' ?></button>
    <?php if ($editData): ?><a class="btn" href="index.php" style="background:#60758a;">Batal</a><?php endif; ?>
</p>
</form></section>

<section class="panel">
    <h2>Daftar Mahasiswa (<?= count($_SESSION['mahasiswa']) ?>)</h2>
    <div class="scroll">
        <table>
            <thead>
                <tr>
                    <?php foreach ($keys as $label): ?>
                        <th><?= aman($label) ?></th>
                    <?php endforeach; ?>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($_SESSION['mahasiswa'])): ?>
                <tr><td colspan="9" style="text-align: center; color: #60758a;">Belum ada data.</td></tr>
            <?php else: ?>
                <?php foreach ($_SESSION['mahasiswa'] as $m): ?>
                <tr>
                    <?php foreach (array_keys($keys) as $key): 
                        $method = 'get' . ucfirst($key);
                        $val = method_exists($m, $method) ? $m->$method() : '';
                    ?>
                        <td><?= aman((string)$val) ?></td>
                    <?php endforeach; ?>
                    <td>
                        <div class="actions">
                            <a class="btn" href="?edit=<?= urlencode($m->getNim()) ?>">Edit</a>
                            <form method="post" onsubmit="return confirm('Hapus data ini?')">
                                <input type="hidden" name="csrf" value="<?= aman($_SESSION['csrf']) ?>">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="nim" value="<?= aman($m->getNim()) ?>">
                                <button class="danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p><small class="muted">Data disimpan pada PHP Session dengan konsep Enkapsulasi PBO.</small></p>
</section>

</main>
</body>
</html>