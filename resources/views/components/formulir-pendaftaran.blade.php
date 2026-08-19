<style>
    .fp-document {
        font-family: DejaVu Sans, Arial, sans-serif;
        color: #111827;
        width: 100%;
        font-size: 11pt;
        line-height: 1.45;
    }

    .fp-kop {
        width: 100%;
        border-bottom: 3px solid #16423c;
        padding-bottom: 6px;
        margin-bottom: 14px;
    }

    .fp-kop table {
        width: 100%;
        border-collapse: collapse;
    }

    .fp-kop-logo {
        text-align: center;
        vertical-align: middle;
        width: 24%;
    }

    .fp-kop-logo img {
        max-width: 70px;
        max-height: 70px;
    }

    .fp-kop-text {
        vertical-align: middle;
        text-align: center;
    }

    .fp-kop-title {
        font-size: 15pt;
        font-weight: bold;
        letter-spacing: 1px;
    }

    .fp-kop-sub {
        font-size: 11pt;
        font-weight: bold;
        margin-top: 2px;
    }

    .fp-kop-detail {
        font-size: 8.5pt;
        margin-top: 3px;
        color: #374151;
    }

    .fp-title {
        text-align: center;
        font-weight: bold;
        text-decoration: underline;
        font-size: 13pt;
        margin: 12px 0 4px;
    }

    .fp-subtitle {
        text-align: center;
        font-size: 9pt;
        margin-bottom: 14px;
        color: #374151;
    }

    .fp-data {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 8px;
    }

    .fp-data td {
        border: 1px solid #111827;
        padding: 5px 8px;
        vertical-align: top;
    }

    .fp-data .fp-label {
        width: 32%;
        font-weight: bold;
    }

    .fp-data .fp-value {
        width: 68%;
    }

    .fp-keluarga-note {
        font-size: 8.5pt;
        color: #374151;
        margin-bottom: 10px;
    }

    .fp-sign-wrap {
        width: 100%;
        margin-top: 30px;
    }

    .fp-sign-center {
        width: 100%;
        text-align: center;
        vertical-align: top;
    }

    .fp-sign-text {
        font-size: 10pt;
    }

    .fp-sign-space {
        height: 34mm;
    }

    .fp-sign-image {
        height: 26mm;
    }

    .fp-sign-image img {
        max-height: 26mm;
        max-width: 60mm;
    }

    .fp-sign-name {
        margin-top: 2mm;
        font-weight: bold;
        text-decoration: underline;
        font-size: 10pt;
    }
</style>

<div class="fp-document">
    {{-- Kop Surat --}}
    <div class="fp-kop">
        <table>
            <tr>
                <td class="fp-kop-logo">
                    <img src="{{ $logoDataUri }}" alt="Logo">
                </td>
                <td class="fp-kop-text">
                    <div class="fp-kop-title">RKM AL-JANNAH</div>
                    <div class="fp-kop-sub">RUKUN WARGA MUSLIMIN KELURAHAN JANNAH</div>
                    <div class="fp-kop-detail">
                        Jl. Contoh No. 1, RT 001/002, Kelurahan Jannah, Indonesia<br>
                        Telepon: +62 812-3456-7890 &nbsp;|&nbsp; Email: info@aljannah.org
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Judul --}}
    <div class="fp-title">FORMULIR PENDAFTARAN ANGGOTA</div>
    <div class="fp-subtitle">RKM AL-JANNAH &mdash; Nomor Pendaftaran: {{ $pendaftar['nomor_pendaftaran'] }}</div>

    {{-- Data Pendaftar --}}
    <table class="fp-data">
        <tr>
            <td class="fp-label">Nama Lengkap</td>
            <td class="fp-value">{{ $pendaftar['nama'] }}</td>
        </tr>
        <tr>
            <td class="fp-label">Jenis Kelamin</td>
            <td class="fp-value">{{ $pendaftar['jenis_kelamin'] }}</td>
        </tr>
        <tr>
            <td class="fp-label">Tempat, Tanggal Lahir</td>
            <td class="fp-value">{{ $pendaftar['tanggal_lahir'] }}</td>
        </tr>
        <tr>
            <td class="fp-label">Alamat Lengkap</td>
            <td class="fp-value">{{ $pendaftar['alamat'] }}</td>
        </tr>
        <tr>
            <td class="fp-label">RT/RW</td>
            <td class="fp-value">{{ $pendaftar['rt_rw'] }}</td>
        </tr>
        <tr>
            <td class="fp-label">Kelurahan</td>
            <td class="fp-value">{{ $pendaftar['kelurahan'] }}</td>
        </tr>
        <tr>
            <td class="fp-label">Nomor HP</td>
            <td class="fp-value">{{ $pendaftar['telepon'] }}</td>
        </tr>
        <tr>
            <td class="fp-label">Email</td>
            <td class="fp-value">{{ $pendaftar['email'] }}</td>
        </tr>
        <tr>
            <td class="fp-label">Nama yang Ditanggung</td>
            <td class="fp-value">
                @forelse($keluarga as $nama)
                    <div>&bull; {{ $nama }}</div>
                @empty
                    -
                @endforelse
            </td>
        </tr>
        <tr>
            <td class="fp-label">Tanggal Daftar</td>
            <td class="fp-value">{{ $pendaftar['tanggal_daftar'] }}</td>
        </tr>
    </table>

    <div class="fp-keluarga-note">* Nama yang ditanggung adalah anggota keluarga yang terdaftar dalam kartu anggota.</div>

    {{-- Tanda Tangan --}}
    <div class="fp-sign-wrap">
        <div class="fp-sign-center">
            <div class="fp-sign-text">Ketua RKM Al-Jannah,</div>
            @if($signed && !empty($ketuaData['signature_data_uri']))
                <div class="fp-sign-image">
                    <img src="{{ $ketuaData['signature_data_uri'] }}" alt="Tanda Tangan Ketua">
                </div>
            @else
                <div class="fp-sign-space"></div>
            @endif
            <div class="fp-sign-name">{{ $ketuaData['nama'] }}</div>
        </div>
    </div>
</div>