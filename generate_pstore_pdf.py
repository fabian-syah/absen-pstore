import sys
import os
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, HRFlowable
)
from reportlab.pdfgen import canvas

class NumberedCanvas(canvas.Canvas):
    def __init__(self, *args, **kwargs):
        super(NumberedCanvas, self).__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_page_number(num_pages)
            super(NumberedCanvas, self).showPage()
        super(NumberedCanvas, self).save()

    def draw_page_number(self, page_count):
        self.saveState()
        self.setFont("Helvetica", 8)
        self.setFillColor(colors.HexColor("#64748B"))
        
        # Header line (pages 2+)
        if self._pageNumber > 1:
            self.drawString(36, 810, "PStore Indonesia — Daftar Alamat Cabang Resmi (Bukan Kantor Pusat)")
            self.setStrokeColor(colors.HexColor("#E2E8F0"))
            self.setLineWidth(0.5)
            self.line(36, 804, 559, 804)

        # Footer
        footer_text = f"Halaman {self._pageNumber} dari {page_count}"
        self.drawRightString(559, 20, footer_text)
        self.drawString(36, 20, "Dokumen Verifikasi Resmi Cabang PStore | Sumber: Google Maps & Instagram Resmi")
        self.setStrokeColor(colors.HexColor("#E2E8F0"))
        self.setLineWidth(0.5)
        self.line(36, 28, 559, 28)
        self.restoreState()

def build_pdf(filename="Daftar_Alamat_Cabang_PStore_Resmi.pdf"):
    doc = SimpleDocTemplate(
        filename,
        pagesize=A4,
        leftMargin=36,
        rightMargin=36,
        topMargin=46,
        bottomMargin=38
    )

    styles = getSampleStyleSheet()
    
    # Custom styles
    title_style = ParagraphStyle(
        'DocTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=16,
        leading=20,
        textColor=colors.HexColor("#0F172A"),
        spaceAfter=3
    )
    
    subtitle_style = ParagraphStyle(
        'DocSubTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=9.5,
        leading=13,
        textColor=colors.HexColor("#0284C7"),
        spaceAfter=8
    )
    
    section_style = ParagraphStyle(
        'SectionHeading',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=10,
        leading=14,
        textColor=colors.HexColor("#1E293B"),
        keepWithNext=True,
        spaceBefore=8,
        spaceAfter=4
    )
    
    th_style = ParagraphStyle(
        'TableHeader',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8,
        leading=10.5,
        textColor=colors.white
    )
    
    cell_branch = ParagraphStyle(
        'CellBranch',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=7.5,
        leading=10,
        textColor=colors.HexColor("#0F172A")
    )
    
    cell_address = ParagraphStyle(
        'CellAddress',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=7.2,
        leading=9.5,
        textColor=colors.HexColor("#334155")
    )
    
    cell_ig = ParagraphStyle(
        'CellIG',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=7.2,
        leading=9.5,
        textColor=colors.HexColor("#0369A1")
    )
    
    notice_style = ParagraphStyle(
        'NoticeText',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=7.2,
        leading=10,
        textColor=colors.HexColor("#991B1B")
    )

    story = []

    # Title & Header
    story.append(Paragraph("DAFTAR LENGKAP ALAMAT RESMI CABANG PSTORE", title_style))
    story.append(Paragraph("Verifikasi Resmi Google Maps & Akun Instagram Cabang (Bukan Kantor Pusat Condet)", subtitle_style))
    story.append(HRFlowable(width="100%", thickness=1.5, color=colors.HexColor("#0284C7"), spaceBefore=0, spaceAfter=6))

    # Anti-penipuan banner
    notice_data = [[
        Paragraph(
            "<b>PANDUAN VERIFIKASI KEAMANAN (ANTI-PENIPUAN):</b> Seluruh cabang resmi PStore <b>TIDAK melayani</b> transaksi pembayaran atau transfer "
            "melalui DM Instagram maupun kontak nomor WhatsApp pribadi. Pelanggan wajib bertransaksi langsung di <b>toko fisik (offline store)</b> "
            "atau platform marketplace resmi yang terverifikasi. Seluruh alamat dan username Instagram di bawah ini telah dicocokkan dengan titik fisik Google Maps.",
            notice_style
        )
    ]]
    notice_table = Table(notice_data, colWidths=[523])
    notice_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, -1), colors.HexColor("#FEF2F2")),
        ('BOX', (0, 0), (-1, -1), 1, colors.HexColor("#FCA5A5")),
        ('TOPPADDING', (0, 0), (-1, -1), 4),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 4),
        ('LEFTPADDING', (0, 0), (-1, -1), 6),
        ('RIGHTPADDING', (0, 0), (-1, -1), 6),
    ]))
    story.append(notice_table)
    story.append(Spacer(1, 4))

    # Sections data
    sections = [
        {
            "region": "1. DKI JAKARTA (CABANG NON-PUSAT)",
            "branches": [
                ("PStore Slipi", "Jl. Bang Pitung (Rawabelong) No. 356, Sukabumi Utara, Kebon Jeruk, Jakarta Barat", "@pstore_jakbarrr"),
                ("PStore Lenteng Agung", "Jl. Raya Lenteng Agung (Lenteng Agung Pass, samping B-Quik Kuning / dekat AEON Mall Tanjung Barat), Jagakarsa, Jakarta Selatan", "@pstore_jaksel"),
                ("PStore Tebet", "Jl. Prof. DR. Soepomo No. 143, Tebet Barat, Jakarta Selatan (Seberang Hotel Amaris / Samping Honda AHASS)", "@tebet.pstore"),
                ("PStore Buaran", "Jl. Raden Inten II No. 20, Klender, Duren Sawit, Jakarta Timur (Seberang SMKN 48 Jakarta)", "@buaran_pstore"),
                ("PStore Kelapa Gading", "Jl. Boulevard Barat Raya Blok XC No. 5, Kelapa Gading Barat, Jakarta Utara (Samping Eiger)", "@pstore_kelapa_gadinggg"),
                ("PStore Sunter", "Jl. Danau Sunter Utara Blok F20 No. 23, Sunter Agung, Tanjung Priok, Jakarta Utara (Samping Bebek Kaleyo)", "@pstoresunterrr"),
                ("PStore Salemba", "Jl. Percetakan Negara Raya No. 45, Cempaka Putih Barat, Jakarta Pusat (Samping Kimia Farma)", "@pstore_salemba"),
            ]
        },
        {
            "region": "2. BANTEN & TANGERANG RAYA",
            "branches": [
                ("PStore Tangerang", "Jl. KH. Hasyim Ashari, RT.003/RW.002, Poris Plawad Indah, Kec. Cipondoh, Kota Tangerang, Banten 15141", "@pstore_tangerang"),
                ("PStore Cikupa", "Talagasari, Cikupa, Tangerang Regency, Banten 15710 (Plus Code: QGCF+HM, Jl. Raya Serang / Pantura, Seberang Bundaran Citra Raya, Samping Kopi Calf To Go Cikupa / RSIA Tiara)", "@pstore_cikupa"),
                ("PStore Banten (Serang)", "Depan Hotel Flamenggo Samping Lotte Grosir, Jl. Raya Cilegon Drangong Serang - Banten No.Km.2, RT.5/RW.6, Kepandean, Kec. Serang, Kota Serang, Banten 42182", "@pstore.banten"),
                ("PStore Cilegon", "Jl. Ahmad Yani, Sukmajaya, Kec. Jombang, Kota Cilegon, Banten 42411", "@pstore.cilegon"),
                ("PStore Pamulang", "Jl. Siliwangi No. 14D, Pamulang Barat, Kec. Pamulang, Tangerang Selatan (Seberang Living Plaza Pamulang)", "@pstore_pamulang"),
                ("PStore Ciputat", "Jl. Aria Putra No. 71, Serua Indah, Kec. Ciputat, Tangerang Selatan (Depan Alfamart Kavling)", "@pstoreciputtat"),
            ]
        },
        {
            "region": "3. JAWA BARAT (BODETABEK & JAWA BARAT)",
            "branches": [
                ("PStore Bekasi", "Jl. Pulo Sirih Utama No. 620, RT.001/RW.014, Jaka Setia, Kec. Bekasi Selatan, Kota Bekasi, Jawa Barat 17147 (Depan Mall Grand Galaxy Park / Maps: Pstore Bekasi)", "@bekasi.pstore"),
                ("PStore Cikarang", "Jl. Tarum Barat II Blok E2 No. 25, Jayamukti, Kec. Cikarang Pusat, Kabupaten Bekasi, Jawa Barat 17530 (Area Pasar Bersih Cikarang Baru)", "@cikarangg_pstore"),
                ("PStore Bogor", "Jl. Dr. Sumeru No. 56C, Kebon Kelapa, Bogor Barat, Kota Bogor (Samping Bank BRI / Seberang SPBU Shell Semeru)", "@pstore_bogors"),
                ("PStore Cibinong", "Ruko Nirwana Golden Park Blok C No. 5, Jl. Pakansari, Pakansari, Kec. Cibinong, Kab. Bogor (Dekat Stadion Pakansari)", "@pstore.cibinong"),
                ("PStore Cibubur", "Jl. Alternatif Cibubur No. 59A, Jatikarya, Kec. Jatisampurna, Kota Bekasi (±700 meter dari Mall Ciputra Cibubur)", "@pstore_cibubur"),
                ("PStore Depok", "Jl. Raya Sawangan No. 38, Mampang, Pancoran Mas, Kota Depok (Dekat Mall DTC Sawangan)", "@pstore_depok"),
                ("PStore Bandung Neo", "Jl. Dipati Ukur No. 72E, Lebakgede, Kec. Coblong, Kota Bandung, Jawa Barat 40132 (Samping Hotel Neo / Dekat Kampus Unikom)", "@bandungneo_pstore / @pstore_bandung"),
                ("PStore Bandung Uber", "Jl. A.H. Nasution No. 73, Pasanggrahan, Kec. Ujung Berung, Kota Bandung, Jawa Barat 40615", "@pst0re_ujungberungg"),
                ("PStore Karawang", "Ruko Mediterania Blok A No. 19, Jl. Galuh Mas Raya, Sukaharja, Telukjambe Timur, Kab. Karawang (Samping Bank BRI)", "@pstore_karawangg"),
                ("PStore Cirebon", "Jl. Dr. Cipto Mangunkusumo No. 26, Pekiringan, Kec. Kesambi, Kota Cirebon (Samping Transmart Cirebon)", "@pstore_ciirebon"),
                ("PStore Cianjur", "Jl. KH. Abdullah bin Nuh No. 1, Pamoyanan, Kec. Cianjur, Kabupaten Cianjur", "@pstore_cianjurr"),
                ("PStore Sukabumi", "Jl. R.A. Kosasih No. 115, Ciaul, Subangjaya, Kec. Cikole, Kota Sukabumi (Samping Klinik Permata Ciaul)", "@pstore_sukabummii"),
            ]
        },
        {
            "region": "4. JAWA TENGAH & D.I. YOGYAKARTA",
            "branches": [
                ("PStore Jogja", "Ruko DTA Square, Kledokan, Caturtunggal, Kec. Depok, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55282", "@jogjaakarta_pstore"),
                ("PStore Semarang", "Jl. Veteran No. 50, Randusari, Kec. Semarang Sel., Kota Semarang, Jawa Tengah 50249 (Area belakang Polda Jateng)", "@pstore_psis_semarang"),
                ("PStore Solo", "Samping BPR/BSI, Nusukan, Jl. Kapten Piere Tendean Ruko Harlin, Nusukan, Kec. Banjarsari, Kota Surakarta, Jawa Tengah 57135", "@pstoresolo_jateng"),
                ("PStore Purwokerto", "Jl. Profesor DR. HR Boenyamin No. 80, Pakembaran, Bancarkembar, Kec. Purwokerto Utara, Kabupaten Banyumas, Jawa Tengah 53121", "@pst0re.purwokert0"),
            ]
        },
        {
            "region": "5. JAWA TIMUR",
            "branches": [
                ("PStore Surabaya", "Jl. Raya Bratang Binangun No. 29, Baratajaya, Kec. Gubeng, Surabaya, Jawa Timur 60284", "@pstore_surabayabratang"),
                ("PStore Malang", "Jl. S. Supriadi No. 23, Sukun, Kec. Sukun, Kota Malang, Jawa Timur", "@pstore_mallang"),
            ]
        },
        {
            "region": "6. SUMATERA & KEPULAUAN RIAU",
            "branches": [
                ("PStore Medan", "Sebrang kampus UISU, Jl. Sisingamangaraja I No. 160-B, Siti Rejo I, Kec. Medan Kota, Kota Medan, Sumatera Utara 20217", "@pstore_meedan"),
                ("PStore Batam Center", "Deretan SPBU, Komplek Palm Regency, Batam Center, Gelael, Jl. Laksamana Bintan Ruko Blok B13A, Taman Baloi, Kec. Batam Kota, Kota Batam, Kepulauan Riau 29464", "@pstore_batam"),
                ("PStore Batam Batu Aji", "Ruko SP Plaza Blok Jati No. 1–2, Sagulung / Batu Aji, Kota Batam, Kepulauan Riau", "@pstore_batam"),
                ("PStore Pekanbaru", "Jl. Suka Karya No. 3, Kelurahan Sialang Munggu, Kec. Tampan, Kota Pekanbaru, Riau 28293", "@ps.store_pekanbaru"),
                ("PStore Padang", "Jl. Dr. Moh. Hatta No. 42, Anduring, Kec. Kuranji, Kota Padang, Sumatera Barat 25176", "@pstorepadang"),
                ("PStore Palembang", "Jl. Mayor Ruslan No. 8, 9 Ilir, Kec. Ilir Tim. II, Kota Palembang, Sumatera Selatan 30114", "@pstore.palembangg"),
                ("PStore Jambi", "Jl. Sumantri Brojonegoro, Payo Lebar, Kec. Jelutung, Kota Jambi, Jambi 36124", "@pstore.jbi"),
                ("PStore Bengkulu", "674G+6QF, Jl. Meranti, Sawah Lebar, Kec. Ratu Agung, Kota Bengkulu, Bengkulu 38222 (Samping Indomaret Meranti)", "@pstore_bengkulu"),
                ("PStore Lampung", "Jl. P. Antasari No. 40, Tj. Baru, Kec. Kedamaian, Kota Bandar Lampung, Lampung 35122", "@pstore_lampung"),
                ("PStore Banda Aceh", "Jl. Dr. Mr. Mohd Hasan No. 96a, Batoh, Kec. Lueng Bata, Kota Banda Aceh, Aceh 23245", "@pstore.aceh"),
                ("PStore Bangka Belitung", "Jl. KH. Abdurahman Siddik, Gedung Nasional, Kec. Taman Sari, Kota Pangkal Pinang, Kepulauan Bangka Belitung 33684 (Depan Puncak Mall)", "@pstore.bangka"),
            ]
        },
        {
            "region": "7. KALIMANTAN",
            "branches": [
                ("PStore Balikpapan", "Jln. Soekarno-Hatta Komplek Bumi Nirwana Indah, Blk. B No. 06, Batu Ampar, Kec. Balikpapan Utara, Kota Balikpapan, Kalimantan Timur 76129 (Samping Hotel Platinum)", "@pstore_balikpapan"),
                ("PStore Samarinda", "Jl. Gatot Subroto No. 22a, Bandara, Kec. Sungai Pinang, Kota Samarinda, Kalimantan Timur 75117", "@pstore_samarinda_kaltim"),
                ("PStore Banjarmasin", "Jl. Kp. Melayu Darat, Melayu, Kec. Banjarmasin Tengah, Kota Banjarmasin, Kalimantan Selatan 70121 (Samping BNI Kampung Melayu)", "@pstore_banjarmasinnnn"),
                ("PStore Pontianak", "Jl. Jendral Urip, Tengah, Kec. Pontianak Kota, Kota Pontianak, Kalimantan Barat 78243 (Depan Matahari Mall)", "@pstorepontianak_new"),
                ("PStore Palangka Raya", "Jl. Seth Adji No. 113, Langkai, Kec. Pahandut, Kota Palangka Raya, Kalimantan Tengah 74874 (Maps iPhone: Jalan Seth Adji No. 5, Palangkaraya 73113)", "@pstore.palangkarayya"),
                ("PStore Tarakan", "Jl. Cendrawasih, Karang Anyar Pantai, Kec. Tarakan Bar., Kota Tarakan, Kalimantan Utara 77111", "@pstore.tarakan"),
                ("PStore Sangatta", "Jl. Wolter Monginsidi, Sangatta Utara, Kec. Sangatta Utara, Kabupaten Kutai Timur, Kalimantan Timur 75683", "@pstoresangatta_"),
            ]
        },
        {
            "region": "8. BALI, NUSA TENGGARA, & SULAWESI",
            "branches": [
                ("PStore Bali", "Jl. Teuku Umar No. 102, Dauh Puri Klod, Kec. Denpasar Bar., Kota Denpasar, Bali 80113", "@pstore_denpasar"),
                ("PStore Lombok", "Samping We Drink, Jl. Pejanggik No. 28, Cakranegara Bar., Mataram Barat, Kota Mataram, Nusa Tenggara Bar. 83239", "@lombokpstore"),
                ("PStore Makassar (Toddopuli)", "Jl. Toddopuli 7 No. 7, Borong, Kec. Manggala, Kota Makassar, Sulawesi Selatan 90222", "@pstore_maakassar"),
                ("PStore Makassar (Perintis)", "Jl. Perintis Kemerdekaan KM 12, Pai, Kec. Tamalanrea, Kota Makassar, Sulawesi Selatan 90245 (Samping Swalayan Bintang / Satu Sama)", "@pstore_maakassar"),
                ("PStore Manado", "Jl. Piere Tendean No. 23, Sario Utara, Kec. Sario, Kota Manado, Sulawesi Utara 95111 (Kawasan Megamas)", "@pstore_manado"),
                ("PStore Palu", "Jl. Emmy Saelan No. 4, Lolu Sel., Kec. Palu Sel., Kota Palu, Sulawesi Tengah 94111 (Seberang Pizza Hut / Zona Coffee)", "@pstorepalu"),
                ("PStore Gorontalo", "Jl. Prof. Dr. H.B. Jassin No. 3, Limba B, Kota Sel., Kota Gorontalo, Gorontalo 96136 (Perempatan Lampu Merah Gelael)", "@pstore.gorontalooo"),
            ]
        }
    ]

    col_widths = [105, 305, 113]

    for sec in sections:
        story.append(Paragraph(sec["region"], section_style))
        
        table_rows = []
        table_rows.append([
            Paragraph("Nama Cabang", th_style),
            Paragraph("Alamat Lengkap & Patokan (Google Maps)", th_style),
            Paragraph("Instagram Resmi", th_style)
        ])
        
        for name, addr, ig in sec["branches"]:
            table_rows.append([
                Paragraph(name, cell_branch),
                Paragraph(addr, cell_address),
                Paragraph(ig, cell_ig)
            ])
            
        t = Table(table_rows, colWidths=col_widths, repeatRows=1)
        
        t_style = [
            ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor("#1E293B")),
            ('TEXTCOLOR', (0, 0), (-1, 0), colors.white),
            ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
            ('VALIGN', (0, 0), (-1, -1), 'TOP'),
            ('TOPPADDING', (0, 0), (-1, -1), 3),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 3),
            ('LEFTPADDING', (0, 0), (-1, -1), 4.5),
            ('RIGHTPADDING', (0, 0), (-1, -1), 4.5),
            ('GRID', (0, 0), (-1, -1), 0.5, colors.HexColor("#CBD5E1")),
        ]
        
        for i in range(1, len(table_rows)):
            if i % 2 == 0:
                t_style.append(('BACKGROUND', (0, i), (-1, i), colors.HexColor("#F8FAFC")))
            else:
                t_style.append(('BACKGROUND', (0, i), (-1, i), colors.white))
                
        t.setStyle(TableStyle(t_style))
        story.append(t)
        story.append(Spacer(1, 5))

    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"PDF berhasil dibuat: {filename}")

if __name__ == "__main__":
    out_pdf = os.path.join(os.getcwd(), "Daftar_Alamat_Cabang_PStore_Resmi.pdf")
    build_pdf(out_pdf)
