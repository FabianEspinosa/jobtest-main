#! /bin/bash

bold=`tput bold`
blue=`tput setaf 4`
black=`tput setaf 0`
white=`tput setaf 7`
reset=`tput sgr0`

if [ -z "$1" ]
  then
    sass src/assets/scss/style.scss static/css/stylesheet.min.css --style compressed
  else
    sass --watch src/assets/scss/style.scss static/css/stylesheet.min.css --style compressed
fi

echo "==> ${blue}${bold}Estilos compilados correctamente${reset}"