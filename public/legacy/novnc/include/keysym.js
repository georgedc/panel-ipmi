var XK_VoidSymbol =                0xffffff, /* Void symbol */

XK_BackSpace =                   0xff08, /* Back space, back char */
XK_Tab =                         0xff09,
XK_Linefeed =                    0xff0a, /* Linefeed, LF */
XK_Clear =                       0xff0b,
XK_Return =                      0xff0d, /* Return, enter */
XK_Pause =                       0xff13, /* Pause, hold */
XK_Scroll_Lock =                 0xff14,
XK_Sys_Req =                     0xff15,
XK_Escape =                      0xff1b,
XK_Delete =                      0xffff, /* Delete, rubout */

XK_lessthan =                    0xff3c, /* LESS-THAN SIGN */
XK_greaterthan =                 0xff3e, /* GREATER-THAN SIGN */
/* Cursor control & motion */

XK_Home =                        0xff50,
XK_Left =                        0xff51, /* Move left, left arrow */
XK_Up =                          0xff52, /* Move up, up arrow */
XK_Right =                       0xff53, /* Move right, right arrow */
XK_Down =                        0xff54, /* Move down, down arrow */
XK_Prior =                       0xff55, /* Prior, previous */
XK_Page_Up =                     0xff55,
XK_Next =                        0xff56, /* Next */
XK_Page_Down =                   0xff56,
XK_End =                         0xff57, /* EOL */
XK_Begin =                       0xff58, /* BOL */


/* Misc functions */

XK_Select =                      0xff60, /* Select, mark */
XK_Print =                       0xff61,
XK_Execute =                     0xff62, /* Execute, run, do */
XK_Insert =                      0xff63, /* Insert, insert here */
XK_Undo =                        0xff65,
XK_Redo =                        0xff66, /* Redo, again */
XK_Menu =                        0xff67,
XK_Find =                        0xff68, /* Find, search */
XK_Cancel =                      0xff69, /* Cancel, stop, abort, exit */
XK_Help =                        0xff6a, /* Help */
XK_Break =                       0xff6b,
XK_Mode_switch =                 0xff7e, /* Character set switch */
XK_script_switch =               0xff7e, /* Alias for mode_switch */
XK_Num_Lock =                    0xff7f,

/* Keypad functions, keypad numbers cleverly chosen to map to ASCII */

XK_KP_Space =                    0xff80, /* Space */
XK_KP_Tab =                      0xff89,
XK_KP_Enter =                    0xff8d, /* Enter */
XK_KP_F1 =                       0xff91, /* PF1, KP_A, ... */
XK_KP_F2 =                       0xff92,
XK_KP_F3 =                       0xff93,
XK_KP_F4 =                       0xff94,
XK_KP_Home =                     0xff95,
XK_KP_Left =                     0xff96,
XK_KP_Up =                       0xff97,
XK_KP_Right =                    0xff98,
XK_KP_Down =                     0xff99,
XK_KP_Prior =                    0xff9a,
XK_KP_Page_Up =                  0xff9a,
XK_KP_Next =                     0xff9b,
XK_KP_Page_Down =                0xff9b,
XK_KP_End =                      0xff9c,
XK_KP_Begin =                    0xff9d,
XK_KP_Insert =                   0xff9e,
XK_KP_Delete =                   0xff9f,
XK_KP_Equal =                    0xffbd, /* Equals */
XK_KP_Multiply =                 0xffaa,
XK_KP_Add =                      0xffab,
XK_KP_Separator =                0xffac, /* Separator, often comma */
XK_KP_Subtract =                 0xffad,
XK_KP_Decimal =                  0xffae,
XK_KP_Divide =                   0xffaf,

XK_KP_0 =                        0xffb0,
XK_KP_1 =                        0xffb1,
XK_KP_2 =                        0xffb2,
XK_KP_3 =                        0xffb3,
XK_KP_4 =                        0xffb4,
XK_KP_5 =                        0xffb5,
XK_KP_6 =                        0xffb6,
XK_KP_7 =                        0xffb7,
XK_KP_8 =                        0xffb8,
XK_KP_9 =                        0xffb9,

/*
 * Auxiliary functions; note the duplicate definitions for left and right
 * function keys;  Sun keyboards and a few other manufacturers have such
 * function key groups on the left and/or right sides of the keyboard.
 * We've not found a keyboard with more than 35 function keys total.
 */

XK_F1 =                          0xffbe,
XK_F2 =                          0xffbf,
XK_F3 =                          0xffc0,
XK_F4 =                          0xffc1,
XK_F5 =                          0xffc2,
XK_F6 =                          0xffc3,
XK_F7 =                          0xffc4,
XK_F8 =                          0xffc5,
XK_F9 =                          0xffc6,
XK_F10 =                         0xffc7,
XK_F11 =                         0xffc8,
XK_L1 =                          0xffc8,
XK_F12 =                         0xffc9,
XK_L2 =                          0xffc9,
XK_F13 =                         0xffca,
XK_L3 =                          0xffca,
XK_F14 =                         0xffcb,
XK_L4 =                          0xffcb,
XK_F15 =                         0xffcc,
XK_L5 =                          0xffcc,
XK_F16 =                         0xffcd,
XK_L6 =                          0xffcd,
XK_F17 =                         0xffce,
XK_L7 =                          0xffce,
XK_F18 =                         0xffcf,
XK_L8 =                          0xffcf,
XK_F19 =                         0xffd0,
XK_L9 =                          0xffd0,
XK_F20 =                         0xffd1,
XK_L10 =                         0xffd1,
XK_F21 =                         0xffd2,
XK_R1 =                          0xffd2,
XK_F22 =                         0xffd3,
XK_R2 =                          0xffd3,
XK_F23 =                         0xffd4,
XK_R3 =                          0xffd4,
XK_F24 =                         0xffd5,
XK_R4 =                          0xffd5,
XK_F25 =                         0xffd6,
XK_R5 =                          0xffd6,
XK_F26 =                         0xffd7,
XK_R6 =                          0xffd7,
XK_F27 =                         0xffd8,
XK_R7 =                          0xffd8,
XK_F28 =                         0xffd9,
XK_R8 =                          0xffd9,
XK_F29 =                         0xffda,
XK_R9 =                          0xffda,
XK_F30 =                         0xffdb,
XK_R10 =                         0xffdb,
XK_F31 =                         0xffdc,
XK_R11 =                         0xffdc,
XK_F32 =                         0xffdd,
XK_R12 =                         0xffdd,
XK_F33 =                         0xffde,
XK_R13 =                         0xffde,
XK_F34 =                         0xffdf,
XK_R14 =                         0xffdf,
XK_F35 =                         0xffe0,
XK_R15 =                         0xffe0,

/* Modifiers */

XK_Shift_L =                     0xffe1, /* Left shift */
XK_Shift_R =                     0xffe2, /* Right shift */
XK_Control_L =                   0xffe3, /* Left control */
XK_Control_R =                   0xffe4, /* Right control */
XK_Caps_Lock =                   0xffe5, /* Caps lock On*/
XK_Shift_Lock =                  0xffe6, /* Shift lock */
XK_Caps_Lock_Off =               0xffe7, /* Caps lock Off*/

XK_Meta_L =                      0xffe7, /* Left meta */
XK_Meta_R =                      0xffe8, /* Right meta */
XK_Alt_L =                       0xffe9, /* Left alt */
XK_Alt_R =                       0xffea, /* Right alt */
XK_Super_L =                     0xffeb, /* Left super */
XK_Super_R =                     0xffec, /* Right super */
XK_Hyper_L =                     0xffed, /* Left hyper */
XK_Hyper_R =                     0xffee, /* Right hyper */

XK_ISO_Level3_Shift = 0xfe03, /* AltGr */

/* JP */
XK_RO = 0x8787,
XK_KATAKANAHIRAGANA = 0xff27, /* カタカナひらかな*/
XK_MUHENKAN = 0xff22, /* 無變換 */
XK_HENKAN = 0xff23, /* 變換 */
XK_ZENKAKUHANKAKU =  0xff2a, /* 全形半形 */

XK_TEST = 0x87,
/*
 * Latin 1
 * (ISO/IEC 8859-1 = Unicode U+0020..U+00FF)
 * Byte 3 = 0
 */

XK_space =                       0x0020, /* U+0020 SPACE */
XK_exclam =                      0x0021, /* U+0021 EXCLAMATION MARK */
XK_quotedbl =                    0x0022, /* U+0022 QUOTATION MARK */
XK_numbersign =                  0x0023, /* U+0023 NUMBER SIGN */
XK_dollar =                      0x0024, /* U+0024 DOLLAR SIGN */
XK_percent =                     0x0025, /* U+0025 PERCENT SIGN */
XK_ampersand =                   0x0026, /* U+0026 AMPERSAND */
XK_apostrophe =                  0x0027, /* U+0027 APOSTROPHE */
XK_quoteright =                  0x0027, /* deprecated */
XK_parenleft =                   0x0028, /* U+0028 LEFT PARENTHESIS */
XK_parenright =                  0x0029, /* U+0029 RIGHT PARENTHESIS */
XK_asterisk =                    0x002a, /* U+002A ASTERISK */
XK_plus =                        0x002b, /* U+002B PLUS SIGN */
XK_comma =                       0x002c, /* U+002C COMMA */
XK_minus =                       0x002d, /* U+002D HYPHEN-MINUS */
XK_period =                      0x002e, /* U+002E FULL STOP */
XK_slash =                       0x002f, /* U+002F SOLIDUS */
XK_0 =                           0x0030, /* U+0030 DIGIT ZERO */
XK_1 =                           0x0031, /* U+0031 DIGIT ONE */
XK_2 =                           0x0032, /* U+0032 DIGIT TWO */
XK_3 =                           0x0033, /* U+0033 DIGIT THREE */
XK_4 =                           0x0034, /* U+0034 DIGIT FOUR */
XK_5 =                           0x0035, /* U+0035 DIGIT FIVE */
XK_6 =                           0x0036, /* U+0036 DIGIT SIX */
XK_7 =                           0x0037, /* U+0037 DIGIT SEVEN */
XK_8 =                           0x0038, /* U+0038 DIGIT EIGHT */
XK_9 =                           0x0039, /* U+0039 DIGIT NINE */
XK_colon =                       0x003a, /* U+003A COLON */
XK_semicolon =                   0x003b, /* U+003B SEMICOLON */
XK_less =                        0x003c, /* U+003C LESS-THAN SIGN */
XK_equal =                       0x003d, /* U+003D EQUALS SIGN */
XK_greater =                     0x003e, /* U+003E GREATER-THAN SIGN */
XK_question =                    0x003f, /* U+003F QUESTION MARK */
XK_at =                          0x0040, /* U+0040 COMMERCIAL AT */
XK_A =                           0x0041, /* U+0041 LATIN CAPITAL LETTER A */
XK_B =                           0x0042, /* U+0042 LATIN CAPITAL LETTER B */
XK_C =                           0x0043, /* U+0043 LATIN CAPITAL LETTER C */
XK_D =                           0x0044, /* U+0044 LATIN CAPITAL LETTER D */
XK_E =                           0x0045, /* U+0045 LATIN CAPITAL LETTER E */
XK_F =                           0x0046, /* U+0046 LATIN CAPITAL LETTER F */
XK_G =                           0x0047, /* U+0047 LATIN CAPITAL LETTER G */
XK_H =                           0x0048, /* U+0048 LATIN CAPITAL LETTER H */
XK_I =                           0x0049, /* U+0049 LATIN CAPITAL LETTER I */
XK_J =                           0x004a, /* U+004A LATIN CAPITAL LETTER J */
XK_K =                           0x004b, /* U+004B LATIN CAPITAL LETTER K */
XK_L =                           0x004c, /* U+004C LATIN CAPITAL LETTER L */
XK_M =                           0x004d, /* U+004D LATIN CAPITAL LETTER M */
XK_N =                           0x004e, /* U+004E LATIN CAPITAL LETTER N */
XK_O =                           0x004f, /* U+004F LATIN CAPITAL LETTER O */
XK_P =                           0x0050, /* U+0050 LATIN CAPITAL LETTER P */
XK_Q =                           0x0051, /* U+0051 LATIN CAPITAL LETTER Q */
XK_R =                           0x0052, /* U+0052 LATIN CAPITAL LETTER R */
XK_S =                           0x0053, /* U+0053 LATIN CAPITAL LETTER S */
XK_T =                           0x0054, /* U+0054 LATIN CAPITAL LETTER T */
XK_U =                           0x0055, /* U+0055 LATIN CAPITAL LETTER U */
XK_V =                           0x0056, /* U+0056 LATIN CAPITAL LETTER V */
XK_W =                           0x0057, /* U+0057 LATIN CAPITAL LETTER W */
XK_X =                           0x0058, /* U+0058 LATIN CAPITAL LETTER X */
XK_Y =                           0x0059, /* U+0059 LATIN CAPITAL LETTER Y */
XK_Z =                           0x005a, /* U+005A LATIN CAPITAL LETTER Z */
XK_bracketleft =                 0x005b, /* U+005B LEFT SQUARE BRACKET */
XK_backslash =                   0x005c, /* U+005C REVERSE SOLIDUS */
XK_bracketright =                0x005d, /* U+005D RIGHT SQUARE BRACKET */
XK_asciicircum =                 0x005e, /* U+005E CIRCUMFLEX ACCENT */
XK_underscore =                  0x005f, /* U+005F LOW LINE */
XK_grave =                       0x0060, /* U+0060 GRAVE ACCENT */
XK_quoteleft =                   0x0060, /* deprecated */
XK_a =                           0x0061, /* U+0061 LATIN SMALL LETTER A */
XK_b =                           0x0062, /* U+0062 LATIN SMALL LETTER B */
XK_c =                           0x0063, /* U+0063 LATIN SMALL LETTER C */
XK_d =                           0x0064, /* U+0064 LATIN SMALL LETTER D */
XK_e =                           0x0065, /* U+0065 LATIN SMALL LETTER E */
XK_f =                           0x0066, /* U+0066 LATIN SMALL LETTER F */
XK_g =                           0x0067, /* U+0067 LATIN SMALL LETTER G */
XK_h =                           0x0068, /* U+0068 LATIN SMALL LETTER H */
XK_i =                           0x0069, /* U+0069 LATIN SMALL LETTER I */
XK_j =                           0x006a, /* U+006A LATIN SMALL LETTER J */
XK_k =                           0x006b, /* U+006B LATIN SMALL LETTER K */
XK_l =                           0x006c, /* U+006C LATIN SMALL LETTER L */
XK_m =                           0x006d, /* U+006D LATIN SMALL LETTER M */
XK_n =                           0x006e, /* U+006E LATIN SMALL LETTER N */
XK_o =                           0x006f, /* U+006F LATIN SMALL LETTER O */
XK_p =                           0x0070, /* U+0070 LATIN SMALL LETTER P */
XK_q =                           0x0071, /* U+0071 LATIN SMALL LETTER Q */
XK_r =                           0x0072, /* U+0072 LATIN SMALL LETTER R */
XK_s =                           0x0073, /* U+0073 LATIN SMALL LETTER S */
XK_t =                           0x0074, /* U+0074 LATIN SMALL LETTER T */
XK_u =                           0x0075, /* U+0075 LATIN SMALL LETTER U */
XK_v =                           0x0076, /* U+0076 LATIN SMALL LETTER V */
XK_w =                           0x0077, /* U+0077 LATIN SMALL LETTER W */
XK_x =                           0x0078, /* U+0078 LATIN SMALL LETTER X */
XK_y =                           0x0079, /* U+0079 LATIN SMALL LETTER Y */
XK_z =                           0x007a, /* U+007A LATIN SMALL LETTER Z */
XK_braceleft =                   0x007b, /* U+007B LEFT CURLY BRACKET */
XK_bar =                         0x007c, /* U+007C VERTICAL LINE */
XK_braceright =                  0x007d, /* U+007D RIGHT CURLY BRACKET */
XK_asciitilde =                  0x007e, /* U+007E TILDE */

XK_nobreakspace =                0x00a0, /* U+00A0 NO-BREAK SPACE */
XK_exclamdown =                  0x00a1, /* U+00A1 INVERTED EXCLAMATION MARK */
XK_cent =                        0x00a2, /* U+00A2 CENT SIGN */
XK_sterling =                    0x00a3, /* U+00A3 POUND SIGN */
XK_currency =                    0x00a4, /* U+00A4 CURRENCY SIGN */
XK_yen =                         0x00a5, /* U+00A5 YEN SIGN */
XK_brokenbar =                   0x00a6, /* U+00A6 BROKEN BAR */
XK_section =                     0x00a7, /* U+00A7 SECTION SIGN */
XK_diaeresis =                   0x00a8, /* U+00A8 DIAERESIS */
XK_copyright =                   0x00a9, /* U+00A9 COPYRIGHT SIGN */
XK_ordfeminine =                 0x00aa, /* U+00AA FEMININE ORDINAL INDICATOR */
XK_guillemotleft =               0x00ab, /* U+00AB LEFT-POINTING DOUBLE ANGLE QUOTATION MARK */
XK_notsign =                     0x00ac, /* U+00AC NOT SIGN */
XK_hyphen =                      0x00ad, /* U+00AD SOFT HYPHEN */
XK_registered =                  0x00ae, /* U+00AE REGISTERED SIGN */
XK_macron =                      0x00af, /* U+00AF MACRON */
XK_degree =                      0x00b0, /* U+00B0 DEGREE SIGN */
XK_plusminus =                   0x00b1, /* U+00B1 PLUS-MINUS SIGN */
XK_twosuperior =                 0x00b2, /* U+00B2 SUPERSCRIPT TWO */
XK_threesuperior =               0x00b3, /* U+00B3 SUPERSCRIPT THREE */
XK_acute =                       0x00b4, /* U+00B4 ACUTE ACCENT */
XK_mu =                          0x00b5, /* U+00B5 MICRO SIGN */
XK_paragraph =                   0x00b6, /* U+00B6 PILCROW SIGN */
XK_periodcentered =              0x00b7, /* U+00B7 MIDDLE DOT */
XK_cedilla =                     0x00b8, /* U+00B8 CEDILLA */
XK_onesuperior =                 0x00b9, /* U+00B9 SUPERSCRIPT ONE */
XK_masculine =                   0x00ba, /* U+00BA MASCULINE ORDINAL INDICATOR */
XK_guillemotright =              0x00bb, /* U+00BB RIGHT-POINTING DOUBLE ANGLE QUOTATION MARK */
XK_onequarter =                  0x00bc, /* U+00BC VULGAR FRACTION ONE QUARTER */
XK_onehalf =                     0x00bd, /* U+00BD VULGAR FRACTION ONE HALF */
XK_threequarters =               0x00be, /* U+00BE VULGAR FRACTION THREE QUARTERS */
XK_questiondown =                0x00bf, /* U+00BF INVERTED QUESTION MARK */
XK_Agrave =                      0x00c0, /* U+00C0 LATIN CAPITAL LETTER A WITH GRAVE */
XK_Aacute =                      0x00c1, /* U+00C1 LATIN CAPITAL LETTER A WITH ACUTE */
XK_Acircumflex =                 0x00c2, /* U+00C2 LATIN CAPITAL LETTER A WITH CIRCUMFLEX */
XK_Atilde =                      0x00c3, /* U+00C3 LATIN CAPITAL LETTER A WITH TILDE */
XK_Adiaeresis =                  0x00c4, /* U+00C4 LATIN CAPITAL LETTER A WITH DIAERESIS */
XK_Aring =                       0x00c5, /* U+00C5 LATIN CAPITAL LETTER A WITH RING ABOVE */
XK_AE =                          0x00c6, /* U+00C6 LATIN CAPITAL LETTER AE */
XK_Ccedilla =                    0x00c7, /* U+00C7 LATIN CAPITAL LETTER C WITH CEDILLA */
XK_Egrave =                      0x00c8, /* U+00C8 LATIN CAPITAL LETTER E WITH GRAVE */
XK_Eacute =                      0x00c9, /* U+00C9 LATIN CAPITAL LETTER E WITH ACUTE */
XK_Ecircumflex =                 0x00ca, /* U+00CA LATIN CAPITAL LETTER E WITH CIRCUMFLEX */
XK_Ediaeresis =                  0x00cb, /* U+00CB LATIN CAPITAL LETTER E WITH DIAERESIS */
XK_Igrave =                      0x00cc, /* U+00CC LATIN CAPITAL LETTER I WITH GRAVE */
XK_Iacute =                      0x00cd, /* U+00CD LATIN CAPITAL LETTER I WITH ACUTE */
XK_Icircumflex =                 0x00ce, /* U+00CE LATIN CAPITAL LETTER I WITH CIRCUMFLEX */
XK_Idiaeresis =                  0x00cf, /* U+00CF LATIN CAPITAL LETTER I WITH DIAERESIS */
XK_ETH =                         0x00d0, /* U+00D0 LATIN CAPITAL LETTER ETH */
XK_Eth =                         0x00d0, /* deprecated */
XK_Ntilde =                      0x00d1, /* U+00D1 LATIN CAPITAL LETTER N WITH TILDE */
XK_Ograve =                      0x00d2, /* U+00D2 LATIN CAPITAL LETTER O WITH GRAVE */
XK_Oacute =                      0x00d3, /* U+00D3 LATIN CAPITAL LETTER O WITH ACUTE */
XK_Ocircumflex =                 0x00d4, /* U+00D4 LATIN CAPITAL LETTER O WITH CIRCUMFLEX */
XK_Otilde =                      0x00d5, /* U+00D5 LATIN CAPITAL LETTER O WITH TILDE */
XK_Odiaeresis =                  0x00d6, /* U+00D6 LATIN CAPITAL LETTER O WITH DIAERESIS */
XK_multiply =                    0x00d7, /* U+00D7 MULTIPLICATION SIGN */
XK_Oslash =                      0x00d8, /* U+00D8 LATIN CAPITAL LETTER O WITH STROKE */
XK_Ooblique =                    0x00d8, /* U+00D8 LATIN CAPITAL LETTER O WITH STROKE */
XK_Ugrave =                      0x00d9, /* U+00D9 LATIN CAPITAL LETTER U WITH GRAVE */
XK_Uacute =                      0x00da, /* U+00DA LATIN CAPITAL LETTER U WITH ACUTE */
XK_Ucircumflex =                 0x00db, /* U+00DB LATIN CAPITAL LETTER U WITH CIRCUMFLEX */
XK_Udiaeresis =                  0x00dc, /* U+00DC LATIN CAPITAL LETTER U WITH DIAERESIS */
XK_Yacute =                      0x00dd, /* U+00DD LATIN CAPITAL LETTER Y WITH ACUTE */
XK_THORN =                       0x00de, /* U+00DE LATIN CAPITAL LETTER THORN */
XK_Thorn =                       0x00de, /* deprecated */
XK_ssharp =                      0x00df, /* U+00DF LATIN SMALL LETTER SHARP S */
XK_agrave =                      0x00e0, /* U+00E0 LATIN SMALL LETTER A WITH GRAVE */
XK_aacute =                      0x00e1, /* U+00E1 LATIN SMALL LETTER A WITH ACUTE */
XK_acircumflex =                 0x00e2, /* U+00E2 LATIN SMALL LETTER A WITH CIRCUMFLEX */
XK_atilde =                      0x00e3, /* U+00E3 LATIN SMALL LETTER A WITH TILDE */
XK_adiaeresis =                  0x00e4, /* U+00E4 LATIN SMALL LETTER A WITH DIAERESIS */
XK_aring =                       0x00e5, /* U+00E5 LATIN SMALL LETTER A WITH RING ABOVE */
XK_ae =                          0x00e6, /* U+00E6 LATIN SMALL LETTER AE */
XK_ccedilla =                    0x00e7, /* U+00E7 LATIN SMALL LETTER C WITH CEDILLA */
XK_egrave =                      0x00e8, /* U+00E8 LATIN SMALL LETTER E WITH GRAVE */
XK_eacute =                      0x00e9, /* U+00E9 LATIN SMALL LETTER E WITH ACUTE */
XK_ecircumflex =                 0x00ea, /* U+00EA LATIN SMALL LETTER E WITH CIRCUMFLEX */
XK_ediaeresis =                  0x00eb, /* U+00EB LATIN SMALL LETTER E WITH DIAERESIS */
XK_igrave =                      0x00ec, /* U+00EC LATIN SMALL LETTER I WITH GRAVE */
XK_iacute =                      0x00ed, /* U+00ED LATIN SMALL LETTER I WITH ACUTE */
XK_icircumflex =                 0x00ee, /* U+00EE LATIN SMALL LETTER I WITH CIRCUMFLEX */
XK_idiaeresis =                  0x00ef, /* U+00EF LATIN SMALL LETTER I WITH DIAERESIS */
XK_eth =                         0x00f0, /* U+00F0 LATIN SMALL LETTER ETH */
XK_ntilde =                      0x00f1, /* U+00F1 LATIN SMALL LETTER N WITH TILDE */
XK_ograve =                      0x00f2, /* U+00F2 LATIN SMALL LETTER O WITH GRAVE */
XK_oacute =                      0x00f3, /* U+00F3 LATIN SMALL LETTER O WITH ACUTE */
XK_ocircumflex =                 0x00f4, /* U+00F4 LATIN SMALL LETTER O WITH CIRCUMFLEX */
XK_otilde =                      0x00f5, /* U+00F5 LATIN SMALL LETTER O WITH TILDE */
XK_odiaeresis =                  0x00f6, /* U+00F6 LATIN SMALL LETTER O WITH DIAERESIS */
XK_division =                    0x00f7, /* U+00F7 DIVISION SIGN */
XK_oslash =                      0x00f8, /* U+00F8 LATIN SMALL LETTER O WITH STROKE */
XK_ooblique =                    0x00f8, /* U+00F8 LATIN SMALL LETTER O WITH STROKE */
XK_ugrave =                      0x00f9, /* U+00F9 LATIN SMALL LETTER U WITH GRAVE */
XK_uacute =                      0x00fa, /* U+00FA LATIN SMALL LETTER U WITH ACUTE */
XK_ucircumflex =                 0x00fb, /* U+00FB LATIN SMALL LETTER U WITH CIRCUMFLEX */
XK_udiaeresis =                  0x00fc, /* U+00FC LATIN SMALL LETTER U WITH DIAERESIS */
XK_yacute =                      0x00fd, /* U+00FD LATIN SMALL LETTER Y WITH ACUTE */
XK_thorn =                       0x00fe, /* U+00FE LATIN SMALL LETTER THORN */
XK_ydiaeresis =                  0x00ff; /* U+00FF LATIN SMALL LETTER Y WITH DIAERESIS */

/******************************************************/
/*             Keyboard Remap                         */
/******************************************************/

Keyboard_Remap = {};

/*English keyboard remap*/
Keyboard_Remap["en"] = {
    substitutions       : [[0x2A, XK_KP_Multiply],
                           [0x2B, XK_KP_Add]]
};

/*Korean keyboard remap*/
Keyboard_Remap["ko"] = {
    substitutions       : [[0x20a9, XK_backslash],
                           [0x1107, XK_q],
                           [0x110c, XK_w],
                           [0x1103, XK_e],
                           [0x1100, XK_r],
                           [0x1109, XK_t],
                           [0x116d, XK_y],
                           [0x1167, XK_u],
                           [0x1163, XK_i],
                           [0x1162, XK_o],
                           [0x1166, XK_p],
                           [0x1106, XK_a],
                           [0x1102, XK_s],
                           [0x110b, XK_d],
                           [0x1105, XK_f],
                           [0x1112, XK_g],
                           [0x1169, XK_h],
                           [0x1165, XK_j],
                           [0x1161, XK_k],
                           [0x1175, XK_l],
                           [0x110f, XK_z],
                           [0x1110, XK_x],
                           [0x110e, XK_c],
                           [0x1111, XK_v],
                           [0x1172, XK_b],
                           [0x116e, XK_n],
                           [0x1173, XK_m]],
    substitutions_shift : [[0x1108, XK_Q],
                           [0x110d, XK_W],
                           [0x1104, XK_E],
                           [0x1101, XK_R],
                           [0x110a, XK_T],
                           [0x1164, XK_O],
                           [0x1168, XK_P]],
    substitutions_altgr : []
};

/*Spanish keyboard remap*/
Keyboard_Remap["sp"] = {
    substitutions       : [[0x27,XK_minus],
                           [0x2A,XK_KP_Multiply],
                           [0x2b,XK_bracketright],
                           [0x2c,XK_comma],
                           [0x2d,XK_slash],
                           [0x2e,XK_period],
                           [0x2f,XK_KP_Divide],
                           [0x3c,XK_lessthan],
                           [0x60,XK_bracketleft],
                           [0xa1,XK_plus],
                           [0xb4,XK_quotedbl],
                           [0xba,XK_grave],
                           [0xe7,XK_backslash],
                           [0xf1,XK_semicolon]],
    substitutions_shift : [[0x22,XK_2],
                           [0xb7,XK_3],
                           [0x26,XK_6],
                           [0x2f,XK_7],
                           [0x28,XK_8],
                           [0x29,XK_9],
                           [0x3a,XK_period],
                           [0x3b,XK_comma],
                           [0x3d,XK_0],
                           [0x3e,XK_greaterthan],
                           [0x3f,XK_minus],
                           [0x5e,XK_bracketleft],
                           [0x5f,XK_slash],
                           [0x2a,XK_bracketright],
                           [0xaa,XK_grave],
                           [0xbf,XK_plus],
                           [0xc7,XK_backslash],
                           [0xd1,XK_semicolon],
                           [0xa8,XK_quotedbl]],
    substitutions_altgr : [[0x5c,XK_asciitilde],
                           [0x7b,XK_quotedbl],
                           [0x7c,XK_1],
                           [0x7d,XK_backslash],
                           [0x7e,XK_4],
                           [0x20ac,XK_5],
                           [0xac,XK_6]]
};

/*French keyboard remap*/
Keyboard_Remap["fr"] = {
    substitutions       : [[0x3c,XK_lessthan],
                           [0x61,XK_q],
                           [0x71,XK_a],
                           [0x7a,XK_w],
                           [0x77,XK_z],
                           [0x31,XK_KP_1],
                           [0x32,XK_KP_2],
                           [0x33,XK_KP_3],
                           [0x34,XK_KP_4],
                           [0x35,XK_KP_5],
                           [0x36,XK_KP_6],
                           [0x37,XK_KP_7],
                           [0x38,XK_KP_8],
                           [0x39,XK_KP_9],
                           [0x30,XK_KP_0],
                           [0x2E,XK_KP_Decimal],
                           [0x2F,XK_KP_Divide],
                           [0x2A,XK_KP_Multiply],
                           [0x2B,XK_KP_Add],
                           [0x2D,XK_KP_Subtract],
                           [0x26,XK_1],
                           [0xe9,XK_2],
                           [0x22,XK_3],
                           [0x27,XK_4],
                           [0x28,XK_5],
                           [0x2d,XK_6],
                           [0xe8,XK_7],
                           [0x5f,XK_8],
                           [0xe7,XK_9],
                           [0xe0,XK_0],
                           [0x29,XK_minus],
                           [0x2a,XK_backslash],
                           [0x24,XK_bracketright],
                           [0x5e,XK_bracketleft],
                           [0xf9,XK_apostrophe],
                           [0x6d,XK_semicolon],
                           [0x21,XK_slash],
                           [0x3a,XK_period],
                           [0x3b,XK_comma],
                           [0x2c,XK_m],
                           [0xb2,XK_grave]],
    substitutions_shift : [[0x3e,XK_greaterthan],
                           [0x41,XK_Q],
                           [0x51,XK_A],
                           [0x57,XK_Z],
                           [0x5a,XK_W],
                           [0x31,XK_1],
                           [0x32,XK_2],
                           [0x33,XK_3],
                           [0x34,XK_4],
                           [0x35,XK_5],
                           [0x36,XK_6],
                           [0x37,XK_7],
                           [0x38,XK_8],
                           [0x39,XK_9],
                           [0x30,XK_0],
                           [0xb0,XK_underscore],
                           [0xb5,XK_bar],
                           [0xa3,XK_braceright],
                           [0xa8,XK_braceleft],
                           [0x25,XK_quotedbl],
                           [0x4d,XK_colon],
                           [0xa7,XK_question],
                           [0x2f,XK_greater],
                           [0x2e,XK_less],
                           [0x3f,XK_M]],
    substitutions_altgr : [[0x7e,XK_2],
                           [0x23,XK_3],
                           [0x7b,XK_4],
                           [0x5b,XK_5],
                           [0x7c,XK_6],
                           [0x60,XK_7],
                           [0x5c,XK_8],
                           [0x5e,XK_9],
                           [0x40,XK_0],
                           [0x20ac,XK_e],
                           [0x5d,XK_minus],
                           [0x7d,XK_equal],
                           [0xa4,XK_bracketright]]
};

/*Deutsch keyboard remap*/
Keyboard_Remap["de"] = {
    substitutions       : [[0x5e,XK_grave],
                           [0xdf,XK_minus],
                           [0xb4,XK_equal],
                           [0x23,XK_backslash],
                           [0x7a,XK_y],
                           [0xfc,XK_bracketleft],
                           [0xf6,XK_semicolon],
                           [0xe4,XK_apostrophe],
                           [0x79,XK_z],
                           [0x2d,XK_slash],
                           [0x2A,XK_KP_Multiply],
                           [0x2B,XK_KP_Add],
                           [0x2C,XK_KP_Decimal],
                           [0x31,XK_KP_1],
                           [0x32,XK_KP_2],
                           [0x33,XK_KP_3],
                           [0x34,XK_KP_4],
                           [0x35,XK_KP_5],
                           [0x36,XK_KP_6],
                           [0x37,XK_KP_7],
                           [0x38,XK_KP_8],
                           [0x39,XK_KP_9],
                           [0x30,XK_KP_0],
                           [0x2E,XK_KP_Decimal],
                           [0x2F,XK_KP_Divide]],
    substitutions_shift : [[0xb0,XK_grave],
                           [0x22,XK_2],
                           [0xa7,XK_3],
                           [0x26,XK_6],
                           [0x2f,XK_7],
                           [0x28,XK_8],
                           [0x29,XK_9],
                           [0x3d,XK_0],
                           [0x3f,XK_minus],
                           [0x60,XK_equal],
                           [0x5a,XK_y],
                           [0xdc,XK_bracketleft],
                           [0x2a,XK_bracketright],
                           [0xd6,XK_semicolon],
                           [0xc4,XK_apostrophe],
                           [0x59,XK_z],
                           [0x3b,XK_comma],
                           [0x3a,XK_period],
                           [0x5f,XK_slash]],
    substitutions_altgr : [[0xb2,XK_2],
                           [0xb3,XK_3],
                           [0x7b,XK_7],
                           [0x5b,XK_8],
                           [0x5d,XK_9],
                           [0x7d,XK_0],
                           [0x5c,XK_minus],
                           [0x40,XK_q],
                           [0x20ac,XK_e],
                           [0x7e,XK_plus],
                           [0xb5,XK_m]]
};

/*Italiano keyboard remap*/
Keyboard_Remap["it"] = {
    substitutions       : [[0x27,XK_minus],
                           [0x2A,XK_KP_Multiply],
                           [0x2b,XK_bracketright],
                           [0x2d,XK_slash],
                           [0x2f,XK_KP_Divide],
                           [0x3c,XK_lessthan],
                           [0xe8,XK_bracketleft],
                           [0xec,XK_plus],
                           [0xe0,XK_quotedbl],
                           [0x5c,XK_grave],
                           [0xf9,XK_backslash],
                           [0xf2,XK_semicolon]],
    substitutions_shift : [[0x22,XK_2],
                           [0xa3,XK_3],
                           [0x26,XK_6],
                           [0x2f,XK_7],
                           [0x28,XK_8],
                           [0x29,XK_9],
                           [0x3a,XK_period],
                           [0x3b,XK_comma],
                           [0x3d,XK_0],
                           [0x3e,XK_greaterthan],
                           [0x3f,XK_minus],
                           [0xe9,XK_bracketleft],
                           [0x5f,XK_slash],
                           [0x2a,XK_bracketright],
                           [0x7c,XK_grave],
                           [0x5e,XK_plus],
                           [0xa7,XK_backslash],
                           [0xe7,XK_semicolon],
                           [0xb0,XK_quotedbl]],
    substitutions_altgr : [[0x23,XK_quotedbl],
                           [0x40,XK_semicolon],
                           [0x5b,XK_bracketleft],
                           [0x5d,XK_bracketright],
                           [0x20ac, XK_4]]
};

/*United Kingdom keyboard remap*/
Keyboard_Remap["en-gb"] = {
    substitutions: [       [0x23,XK_F33],
                           [0x5c,XK_F31],
                           [0x20ac,XK_lessthan],
    ],
    substitutions_shift: [ [0x7e,XK_F33],
                           [0x7c,XK_F31],
                           [0xac,XK_grave],
                           [0x22,XK_at],
                           [0xa3,XK_numbersign],
                           [0x40,XK_apostrophe],
    ],
    substitutions_altgr: [ [0x5c,XK_F33],
                           [0x20ac,XK_4],
                           [0xe1,XK_a],
                           [0xe9,XK_e],
                           [0xed,XK_i],
                           [0xf3,XK_o],
                           [0xfa,XK_u],
                           [0xa6,XK_grave]]
};

/*Japan keyboard remap*/
Keyboard_Remap["ja"] = {
    substitutions:       [ [0x306c, XK_1],
                           [0x3075, XK_2],
                           [0x3042, XK_3],
                           [0x3046, XK_4],
                           [0x3048, XK_5],
                           [0x304a, XK_6],
                           [0x3084, XK_7],
                           [0x3086, XK_8],
                           [0x3088, XK_9],
                           [0x308f, XK_0],
                           [0x307b, XK_minus],
                           [0x3078, XK_equal],
                           [0x305f, XK_q],
                           [0x3066, XK_w],
                           [0x3044, XK_e],
                           [0x3059, XK_r],
                           [0x304b, XK_t],
                           [0x3093, XK_y],
                           [0x306a, XK_u],
                           [0x306b, XK_i],
                           [0x3089, XK_o],
                           [0x305b, XK_p],
                           [0xff9e, XK_bracketleft],
                           [0xff9f, XK_bracketright],
                           [0x3061, XK_a],
                           [0x3068, XK_s],
                           [0x3057, XK_d],
                           [0x306f, XK_f],
                           [0x304d, XK_g],
                           [0x304f, XK_h],
                           [0x307e, XK_j],
                           [0x306e, XK_k],
                           [0x308a, XK_l],
                           [0x308c, XK_semicolon],
                           [0x3051, XK_apostrophe],
                           [0x3080, XK_F33],
                           [0x4b, XK_F31],
                           [0x3064, XK_z],
                           [0x3055, XK_x],
                           [0x305d, XK_c],
                           [0x3072, XK_v],
                           [0x3053, XK_b],
                           [0x307f, XK_n],
                           [0x3082, XK_m],
                           [0x306d, XK_comma],
                           [0x308b, XK_period],
                           [0x3081, XK_slash],
                        //    [0x67, XK_KATAKANAHIRAGANA],
    ],
    substitutions_shift: [ [0x3041, XK_3],
                           [0x3045, XK_4],
                           [0x3047, XK_5],
                           [0x3049, XK_6],
                           [0x3083, XK_7],
                           [0x3085, XK_8],
                           [0x3087, XK_9],
                           [0x3092, XK_0],
                           [0x00a3, XK_minus],
                           [0x3005, XK_equal], //1
                           [0x305f, XK_q],
                           [0x3066, XK_w],
                           [0x3044, XK_e],
                           [0x3059, XK_r],
                           [0x304b, XK_t],
                           [0x3093, XK_y],
                           [0x306a, XK_u],
                           [0x306b, XK_i],
                           [0x3089, XK_o],
                           [0x305b, XK_p],
                           [0xff9e, XK_bracketleft],
                           [0xff62, XK_bracketright],
                           [0x3061, XK_a],
                           [0x3068, XK_s],
                           [0x3057, XK_d],
                           [0x306f, XK_f],
                           [0x304d, XK_g],
                           [0x304f, XK_h],
                           [0x307e, XK_j],
                           [0x306e, XK_k],
                           [0x308a, XK_l],
                           [0x308c, XK_semicolon],
                           [0x3080, XK_F33],
                           [0x4b, XK_F31],
                           [0x3063, XK_z],
                           [0x3055, XK_x],
                           [0x305d, XK_c],
                           [0x3072, XK_v],
                           [0x3053, XK_b],
                           [0x307f, XK_n],
                           [0x3082, XK_m],
                           [0xff64, XK_comma],
                           [0xff61, XK_period],
                           [0xff65, XK_slash],
                        //    [0x3081, XK_Execute],
                           [0xa6, XK_TEST],
                           [0xff63, XK_backslash],
    ],
    // substitutions_altgr: [ [0x5c,XK_F33],
    //                        [0x20ac,XK_4],
    //                        [0xe1,XK_a],
    //                        [0xe9,XK_e],
    //                        [0xed,XK_i],
    //                        [0xf3,XK_o],
    //                        [0xfa,XK_u],
    //                        [0xa6,XK_grave]]
};