Dim accessApp, db, rs, fso, outFile
Set accessApp = CreateObject("Access.Application")
accessApp.OpenCurrentDatabase "D:\dev\ksr\APLIKASI POS\DATA\dbgrosir - Copy.mdb"
Set db = accessApp.CurrentDb

Set fso = CreateObject("Scripting.FileSystemObject")
Set outFile = fso.CreateTextFile("C:\xampp\htdocs\kios-sabun-parfum-pos\writable\barang_filtered.json", True, True)

Dim sql
sql = "SELECT DISTINCT B.ID_BARANG, B.KODE_BARCODE, B.NAMA, B.KATEGORI, B.SATUAN, " & _
      "B.HRG_BELI, B.HRG_JUAL, B.QTY_PAKET_A, B.HRG_PAKET_A, B.QTY_PAKET_B, B.HRG_PAKET_B, " & _
      "B.QTY_PAKET_C, B.HRG_PAKET_C, B.STOK, B.ISACTIVE " & _
      "FROM BARANG B " & _
      "WHERE (B.ISACTIVE = True OR B.ISACTIVE Is Null) " & _
      "AND ( " & _
      "  B.ID_BARANG IN ( " & _
      "    SELECT P.ID_BARANG FROM PENJUALAN P INNER JOIN MASTERJUAL M ON P.ID_JUAL = M.ID_JUAL " & _
      "    WHERE M.TGL_JUAL >= #2023-01-01# " & _
      "  ) " & _
      "  OR B.ID_BARANG IN ( " & _
      "    SELECT PB.ID_BARANG FROM PEMBELIAN PB INNER JOIN MASTERBELI MB ON PB.ID_BELI = MB.ID_BELI " & _
      "    WHERE MB.TGL_BELI >= #2023-01-01# " & _
      "  ) " & _
      "  OR B.STOK > 0 " & _
      ")"

Set rs = db.OpenRecordset(sql)

outFile.WriteLine "["
Dim first, count
first = True
count = 0

Do While Not rs.EOF
    If Not first Then outFile.WriteLine ","
    first = False
    
    Dim idB, barcode, nama, kat, sat, hb, hj, qa, ha, qb, hb2, qc, hc, stk
    idB = Replace(Replace(Trim(rs("ID_BARANG") & ""), "\", "\\"), """", "\""")
    barcode = Replace(Replace(Trim(rs("KODE_BARCODE") & ""), "\", "\\"), """", "\""")
    nama = Replace(Replace(Trim(rs("NAMA") & ""), "\", "\\"), """", "\""")
    kat = Replace(Replace(Trim(rs("KATEGORI") & ""), "\", "\\"), """", "\""")
    sat = Replace(Replace(Trim(rs("SATUAN") & ""), "\", "\\"), """", "\""")
    sat = Replace(sat, "#", "")
    
    hb = rs("HRG_BELI")
    If IsNull(hb) Then hb = 0
    hj = rs("HRG_JUAL")
    If IsNull(hj) Then hj = 0
    
    qa = rs("QTY_PAKET_A")
    If IsNull(qa) Then qa = 0
    ha = rs("HRG_PAKET_A")
    If IsNull(ha) Then ha = 0
    
    qb = rs("QTY_PAKET_B")
    If IsNull(qb) Then qb = 0
    hb2 = rs("HRG_PAKET_B")
    If IsNull(hb2) Then hb2 = 0
    
    qc = rs("QTY_PAKET_C")
    If IsNull(qc) Then qc = 0
    hc = rs("HRG_PAKET_C")
    If IsNull(hc) Then hc = 0
    
    stk = rs("STOK")
    If IsNull(stk) Then stk = 0
    
    outFile.Write "{""id_barang"":""" & idB & """,""barcode"":""" & barcode & """,""nama"":""" & nama & """,""kategori"":""" & kat & """,""satuan"":""" & sat & """,""harga_beli"":" & hb & ",""harga_jual"":" & hj & ",""qty_paket_a"":" & qa & ",""hrg_paket_a"":" & ha & ",""qty_paket_b"":" & qb & ",""hrg_paket_b"":" & hb2 & ",""qty_paket_c"":" & qc & ",""hrg_paket_c"":" & hc & ",""stok"":" & stk & "}"
    
    count = count + 1
    rs.MoveNext
Loop
outFile.WriteLine ""
outFile.WriteLine "]"

rs.Close
accessApp.CloseCurrentDatabase
accessApp.Quit
WScript.Echo "Total barang aktif & bertransaksi 3 tahun terakhir: " & count
