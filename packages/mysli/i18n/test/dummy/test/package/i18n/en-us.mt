# Standard translations...
@HELLO_WORD  Hello World!

# Numbers + variables
@COMMENTS    Comments
@COMMENTS[0] No comments at the time.
@COMMENTS[1] There's one comment.
@COMMENTS[>] There's {1} comments.

# Multiple variables + numbers.
@HI_MY_NAME_IS_AND_IM_YEARS_OLD[1] Hello, my name is {1}, and I'm 1 year old.
@HI_MY_NAME_IS_AND_IM_YEARS_OLD[>] Hello, my name is {1}, and I'm {2} years old.

# Multi-line
@MULTILINE Hello,
I'm multi-line
text, I'll be converted to one line.

# Multi-line with preserved new-lines
@MULTILINE_KEEP_LINES[nl]
Hello,
the text will stay
in multiple lines!

# With un-escaped HTML tags
@TAGS[html] Hello, I'm <strong>important</strong>.

# Escaped HTML
@TAGS_NO_HTML Hello, I'm <strong>important</strong>.

# Variable with text...
@TEXT_VAR      Please {1 login}!
@TEXT_VAR_MORE You can {1 register} or {2 login} here.
