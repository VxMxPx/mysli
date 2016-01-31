# Assets Template

Include assets with:

```
::use mysli.assets
```

To print tags for particular file(s) or folders use:

```
{ 'css' | assets.tags : 'vendor.package' }
```

... or grab a specific file:

{ 'images/logo.png' | assets.tags : 'vendor.package' }

Tag will always return string, even if there are multiple tags, the opposite is
`links` method, which will always return an array, even if there's only one
link.

To acquire only link(s) (rather than tags):

```
::for link in 'js' | assets.links : 'vendor.package'
    <script type="text/javascript" src="{link}"></script>
::/for
```
