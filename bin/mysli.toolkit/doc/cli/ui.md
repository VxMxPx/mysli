# UI

Use this class to achieve consistent CLI outputs.

Commands, the exception is `template`, will not output string automatically,
but will rather return formated string.

The aim of UI, compared to Output is to provide a consisten UI for command line
interface, therefore content descriptive methods are used (like `title`, `ul`,
`error`, etc...) rather than style descriptive (like `red`, `bold`, etc...).
