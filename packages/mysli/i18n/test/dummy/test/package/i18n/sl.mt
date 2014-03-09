# Standard translations...
@HELLO_WORD  Hej svet!

# Numbers + variables
@COMMENTS    Komentarji
@COMMENTS[0] Trenutno ni komentarjev.
@COMMENTS[1] 1 komentar.
@COMMENTS[2] 2 komentarja.
@COMMENTS[>] {1} komentarjev.

# Multiple variables + numbers.
@HI_MY_NAME_IS_AND_IM_YEARS_OLD[1] Hej, moje ime je {1}, in star sem eno leto.
@HI_MY_NAME_IS_AND_IM_YEARS_OLD[>] Hej, moje ime je {1}, in star sem {2} let.

# Multi-line
@MULTILINE Hello,
To besedilo,
bo prikazano v eni vrstici.

# Multi-line with preserved new-lines
@MULTILINE_KEEP_LINES[nl]
To besedilo,
bo ohranilo več vrstic.

# With un-escaped HTML tags
@TAGS[html] Hello, Jaz sem <strong>pomemben</strong>.

# Escaped HTML
@TAGS_NO_HTML Hej, jaz sem <strong>pomemben</strong>.

# Variable with text...
@TEXT_VAR      Prosimo, {1 prijavite se}!
@TEXT_VAR_MORE Tukaj se lahko {1 registrirate} ali pa {2 prijave}.
