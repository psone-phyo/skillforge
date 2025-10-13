<!DOCTYPE html>
<html lang="en" style="margin:0;padding:0;">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="x-ua-compatible" content="ie=edge"/>
  <title>SkillForge LMS • Account Activation</title>
  <style>
    /* General resets for some clients */
    img { border:0; outline:none; text-decoration:none; }
    table { border-collapse:collapse; }
    body { margin:0; padding:0; background-color:#0B1020; }
    /* Mobile styles */
    @media screen and (max-width: 600px) {
      .container { width:100% !important; }
      .content { padding:20px !important; }
      .otp { font-size:24px !important; letter-spacing:6px !important; }
      .brand-title { font-size:16px !important; }
    }
  </style>
</head>
<body style="margin:0; padding:0; background-color:#0B1020;">
  <!-- Preheader (hidden preview text) -->
  <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
    Your SkillForge LMS activation code is {{ $data }}.
  </div>

  <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#0B1020;">
    <tr>
      <td align="center" style="padding:24px;">
        <!-- Outer container -->
        <table role="presentation" cellpadding="0" cellspacing="0" width="600" class="container" style="width:600px; background:#111739; border-radius:16px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.35);">
          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(180deg,#1B2240,#141B33); padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.08);">
              <table role="presentation" width="100%">
                <tr>
                  <td align="left" style="vertical-align:middle;">
                    <!-- Simple brand mark -->
                    <div style="display:inline-block; width:36px; height:36px; border-radius:10px; background:conic-gradient(from 200deg,#5B8CFF,#7B61FF,#42E3B4,#5B8CFF); box-shadow:0 0 0 1px rgba(255,255,255,0.12) inset;"></div>
                    <span class="brand-title" style="font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif; font-weight:800; color:#E6EBFF; font-size:18px; margin-left:10px; vertical-align:middle;">
                      SkillForge LMS
                    </span>
                  </td>
                  <td align="right" style="vertical-align:middle;">
                    <span style="font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif; color:#9AA6D7; font-size:12px;">
                      Account Activation
                    </span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td class="content" style="padding:28px 28px 10px;">
              <h1 style="margin:0 0 8px; font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif; color:#FFFFFF; font-weight:800; font-size:20px;">
                Hello {{ $user->name ?? 'there' }}!
              </h1>
              <p style="margin:0; font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif; color:#C5CFEE; font-size:14px; line-height:1.6;">
                Use the one-time code below to activate your SkillForge LMS account.
              </p>
            </td>
          </tr>

          <!-- OTP Card -->
          <tr>
            <td style="padding:16px 28px 8px;">
              <table role="presentation" width="100%" style="background:linear-gradient(180deg,#1B2240,#141B33); border:1px solid rgba(255,255,255,0.08); border-radius:14px;">
                <tr>
                  <td style="padding:18px 20px;">
                    <div style="font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif; color:#9AA6D7; font-size:12px; letter-spacing:.12em; text-transform:uppercase; margin-bottom:8px;">
                      Your OTP
                    </div>
                    <div class="otp" style="font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif; color:#FFFFFF; font-weight:800; font-size:28px; letter-spacing:8px; background:#0B1024; border:1px solid rgba(255,255,255,0.12); border-radius:12px; padding:12px 16px; text-align:center;">
                      {{ $data }}
                    </div>
                    <div style="margin-top:10px; font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif; color:#9AA6D7; font-size:12px; line-height:1.6;">
                      This code will expire in 10 minutes. If you didn’t request this, you can safely ignore this email.
                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Quick tips -->
          <tr>
            <td style="padding:12px 28px 8px;">
              <table role="presentation" width="100%" style="background:linear-gradient(180deg,#182145,#141B33); border:1px solid rgba(255,255,255,0.06); border-radius:14px;">
                <tr>
                  <td style="padding:16px 18px;">
                    <div style="font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif; color:#CDE8FF; font-weight:700; font-size:13px; margin-bottom:6px;">
                      Security tips
                    </div>
                    <ul style="margin:0; padding-left:18px; font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif; color:#9AA6D7; font-size:12px; line-height:1.6;">
                      <li>Never share your OTP with anyone — including SkillForge staff.</li>
                      <li>Only enter your code on the official SkillForge LMS site or app.</li>
                      <li>If you didn’t request this code, please reset your password.</li>
                    </ul>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- CTA (optional) -->
          <!--
          <tr>
            <td align="center" style="padding:10px 28px 0;">
              <a href="{{ $ctaUrl ?? '#' }}" style="display:inline-block; font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif; background:linear-gradient(180deg,#5B8CFF,#7B61FF); color:#FFFFFF; text-decoration:none; font-weight:700; font-size:14px; padding:10px 16px; border-radius:10px; border:1px solid rgba(255,255,255,0.2);">
                Verify Now
              </a>
            </td>
          </tr>
          -->

          <!-- Footer -->
          <tr>
            <td style="padding:20px 28px 28px;">
              <table role="presentation" width="100%">
                <tr>
                  <td style="font-family:Inter,Segoe UI,Roboto,Helvetica,Arial,sans-serif; color:#7F89B2; font-size:12px; line-height:1.6; text-align:center;">
                    © {{ date('Y') }} SkillForge LMS. All rights reserved.
                    <br/>
                    You’re receiving this because an OTP was requested for your account.
                    <br/>
                    <a href="{{ config('app.url') }}" style="color:#AFC7FF; text-decoration:none;">Visit SkillForge</a>
                    •
                    <a href="{{ config('app.url') }}/support" style="color:#AFC7FF; text-decoration:none;">Support</a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

        </table>
        <!-- /Outer container -->
      </td>
    </tr>
  </table>
</body>
</html>
