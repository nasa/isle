#!/bin/bash
cd ../cdn/styles/less

# Compile less files for development use.
lessc main.less > ../css-dev/main.css
lessc main_ie8.less > ../css-dev/main_ie8.css
lessc app/list.less > ../css-dev/list.css
lessc app/list_ie7.less > ../css-dev/list_ie7.css
lessc lib/jquery.tagit.less > ../css-dev/jquery.tagit.css
lessc app/views/assets.less > ../css-dev/views/assets.css
lessc app/views/categories.less > ../css-dev/views/categories.css
lessc app/views/versions.less > ../css-dev/views/versions.css

# Compile and minify less files for production use.
lessc --compress main.less > ../main.css
lessc --compress main_ie8.less > ../main_ie8.css
lessc --compress app/list.less > ../list.css
lessc --compress app/list_ie7.less > ../list_ie7.css
lessc --compress lib/jquery.tagit.less > ../jquery.tagit.css
lessc --compress app/views/assets.less > ../views/assets.css
lessc --compress app/views/categories.less > ../views/categories.css
lessc --compress app/views/versions.less > ../views/versions.css

# Combine and minify javascript files.
cd ../../scripts-dev
node r.js -o build.js