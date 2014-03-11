# Standard translations...
@STANDARD  Hello World!

# Numbers + variables
@COMMENTS     Comments
@COMMENTS[0]  No comments.
@COMMENTS[1]  One comment.
@COMMENTS[2+] {1} comments.

@NUMBERS[*7]  I'm ending with 7!
@NUMBERS[4*]  I'm starting with 4!
@NUMBERS[1*2] I'm starting with 1 and ending with 2!

@ODD[*1,*3,*5,*7,*9] I'm odd! :S
@TWO_AND_NINE[2,9]   I'm either two or nine!

# Number ranges
@AGE[0...2]   Hopes
@AGE[2...4]   Will
@AGE[4...5]   Purpose
@AGE[5...12]  Competence
@AGE[13...19] Fidelity
@AGE[20...39] Love
@AGE[40...64] Care
@AGE[65+]     Wisdom

# Multi-line
@MULTILINE Hello,
I'm multi-line
text, I'll be converted to one line.

# Multi-line with preserved new-lines
@MULTILINE_KEEP_LINES[nl]
Hello,
the text will stay
in multiple lines!

# Variable with text...
@TEXT_VAR      Please {1 login}!
@TEXT_VAR_MORE You can {1 register} or {2 login} here.
