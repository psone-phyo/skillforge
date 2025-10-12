{{-- resources/views/certificates/show.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Certificate of Completion • SkillForge LMS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Optional web font (prints fine on most browsers) --}}
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --bg: #0f1320;
      --ink: #1a2035;
      --muted: #6b7391;
      --accent-1: #5B8CFF;
      --accent-2: #7B61FF;
      --accent-3: #42E3B4;
      --paper: #ffffff;
      --border: #d9dff0;
      --gold: #C7A34B;
    }
    html, body {
      margin: 0;
      padding: 0;
      background: #0b1020;
      color: #111;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
    }

    /* Page canvas: A4 Landscape (297mm x 210mm). Most browsers respect mm/cm in print. */
    .page {
      box-sizing: border-box;
      width: 297mm;
      height: 210mm;
      margin: 10mm auto;
      position: relative;
      background: var(--paper);
      box-shadow: 0 15px 35px rgba(0,0,0,0.25);
      overflow: hidden;
    }

    /* Ornamental frame */
    .frame {
      position: absolute;
      inset: 12mm;
      border: 2.5mm solid transparent;
      background:
        linear-gradient(white, white) padding-box,
        conic-gradient(from 210deg, var(--accent-1), var(--accent-2), var(--accent-3), var(--accent-1)) border-box;
      border-radius: 6mm;
    }

    .inner {
      position: absolute;
      inset: 18mm;
      display: grid;
      grid-template-rows: auto auto 1fr auto;
      gap: 10mm;
    }

    /* Header */
    .brand {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }
    .brand-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .logo {
      width: 16mm;
      height: 16mm;
      border-radius: 4mm;
      /* background: conic-gradient(from 200deg, var(--accent-1), var(--accent-2), var(--accent-3), var(--accent-1)); */
      box-shadow: 0 6px 16px rgba(91,140,255,0.35), inset 0 0 14px rgba(255,255,255,0.35);
    }
    .site-title {
      font-weight: 800;
      letter-spacing: .3px;
      color: var(--ink);
      font-size: 15pt;
    }
    .cert-badge {
      color: var(--muted);
      font-weight: 700;
      font-size: 10pt;
      letter-spacing: .12em;
      text-transform: uppercase;
    }

    /* Title */
    .title {
      text-align: center;
    }
    .title h1 {
      margin: 0;
      font-family: "Playfair Display", Georgia, serif;
      font-size: 30pt;
      letter-spacing: .5px;
      color: var(--ink);
    }
    .subtitle {
      margin-top: 6px;
      color: var(--muted);
      font-weight: 600;
      letter-spacing: .2em;
      font-size: 10pt;
      text-transform: uppercase;
    }

    /* Recipient block */
    .recipient {
      text-align: center;
      padding: 4mm 10mm;
    }
    .label {
      color: var(--muted);
      font-size: 10pt;
      letter-spacing: .18em;
      text-transform: uppercase;
      margin-bottom: 6px;
    }
    .name {
      font-family: "Playfair Display", Georgia, serif;
      font-weight: 800;
      font-size: 28pt;
      color: var(--ink);
      line-height: 1.1;
    }
    .for-course {
      margin-top: 8mm;
      font-size: 12pt;
      color: var(--ink);
    }
    .course {
      font-family: "Playfair Display", Georgia, serif;
      font-weight: 700;
      font-size: 18pt;
      margin-top: 3mm;
      color: var(--ink);
    }
    .meta {
      margin-top: 5mm;
      font-size: 10pt;
      color: var(--muted);
    }

    /* Footer signatures */
    .footer {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10mm;
      align-items: end;
    }
    .sig {
      text-align: center;
    }
    .sig-line {
      height: 0;
      border-top: 1.8px solid #aeb7d6;
      margin: 0 8mm 3mm;
    }
    .sig-name {
      font-weight: 700;
      color: var(--ink);
      font-size: 11pt;
    }
    .sig-role {
      color: var(--muted);
      font-size: 9pt;
      letter-spacing: .06em;
    }

    /* Watermark */
    .watermark {
      position: absolute;
      inset: 0;
      display: grid;
      place-items: center;
      pointer-events: none;
    }
    .watermark span {
      font-family: "Playfair Display", Georgia, serif;
      font-size: 80pt;
      font-weight: 800;
      letter-spacing: 6px;
      color: rgba(150,160,200,0.07);
      transform: rotate(-12deg);
      user-select: none;
    }

    /* Print controls */
    .toolbar {
      margin: 10mm auto 0;
      width: 297mm;
      display: flex;
      justify-content: flex-end;
      gap: 8px;
    }
    .btn {
      background: linear-gradient(180deg, #1b2240, #141b33);
      color: #e6eaf5;
      border: 1px solid rgba(255,255,255,0.15);
      padding: 10px 14px;
      border-radius: 10px;
      font-weight: 700;
      cursor: pointer;
    }
    .btn.primary {
      background: linear-gradient(180deg, var(--accent-1), var(--accent-2));
      border-color: rgba(255,255,255,0.35);
      color: #fff;
    }

    /* Print styles */
    @media print {
      body { background: none; }
      .page { margin: 0; box-shadow: none; }
      .toolbar { display: none; }
    }
  </style>
</head>
<body>

  {{-- Optional on-screen toolbar (hidden in print) --}}
  <div class="toolbar">
    <button class="btn" onclick="history.back()">Back</button>
    <button class="btn primary" onclick="window.print()">Print / Save PDF</button>
  </div>

  <main class="page">
    <div class="frame"></div>
    <div class="watermark"><span>SkillForge LMS</span></div>

    <section class="inner">
      <header class="brand">
        <div class="brand-left">
          <div aria-hidden="true">
            <img src="{{ asset('/app_logo.png') }}" class="logo" alt="">
          </div>
          <div class="site-title">SkillForge LMS</div>
        </div>
        <div class="cert-badge">Certificate</div>
      </header>

      <div class="title">
        <h1>Certificate of Completion</h1>
        <div class="subtitle">This certifies that</div>
      </div>

      <section class="recipient">
        <div class="label">Presented to</div>
        <div class="name">
          {{ $certificate->user->name }}
        </div>

        <div class="for-course">for successfully completing</div>
        <div class="course">
          {{ $certificate->course->title}}
        </div>

        <div class="meta">
          {{-- Issue date and certificate number are optional --}}
          @php
            $issuedOn = $certificate->issue_date;
            $certNo   = $certificate->issue_number;
          @endphp
          Issued on: {{ \Carbon\Carbon::parse($issuedOn)->format('F j, Y') }}
          @if($certNo)
            • Certificate No: {{ $certNo }}
          @endif
        </div>
      </section>

      <footer class="footer">
        <div class="sig">
          <div class="sig-line"></div>
          <div class="sig-name">
            {{ $certificate->course->instructor->name }}
          </div>
          <div class="sig-role">Instructor</div>
        </div>
        <div class="sig">
          <div class="sig-line"></div>
          <div class="sig-name">SkillForge LMS</div>
          <div class="sig-role">Authorized Representative</div>
        </div>
      </footer>
    </section>
  </main>

</body>
</html>
