# Standard translations...
@HELLO_WORLD  Hello World!

# Variables
@GREETING              Hi there, {1}!
@GREETING_AND_AGE      Hi there, {1} you're {2} years old.
@GREETING_AND_REGISTER Hi there, please {1 login} or {2 register}.

# Numbers + variables
@COMMENTS     Comments
@COMMENTS[0]  No comments.
@COMMENTS[1]  One comment.
@COMMENTS[2+] {n} comments.

@NUMBERS[*7]  I'm ending with 7!
@NUMBERS[4*]  I'm starting with 4!
@NUMBERS[1*2] I'm starting with 1 and ending with 2!

@ODD[*1,*3,*5,*7,*9] I'm odd! :S
@TWO_AND_NINE[2,9]   Two or nine!

# Number ranges
@AGE[0...1]   Hopes
@AGE[2...3]   Will
@AGE[4]       Purpose
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
