#!/bin/bash
cp .env.example ./.env;

# Puertos a verificar
PORTS=(1025 8025 3306 80 443)
PORTSTOKILL=()
BANNER="ICAgICBfX19fX19fLiAgX19fX19fIC5fX19fX18gICAgICAgX18gIC5fX19fX18gICAuX19fX19fX19fX18uICAgIF9fX19fX18gICBfX19fX19fICAgIC5fX18gIF9fXy4gICAgICBfX18gICAgICAuX18gICBfXy4gIF9fICAgIF9fICAKICAgIC8gICAgICAgfCAvICAgICAgfHwgICBfICBcICAgICB8ICB8IHwgICBfICBcICB8ICAgICAgICAgICB8ICAgfCAgICAgICBcIHwgICBfX19ffCAgIHwgICBcLyAgIHwgICAgIC8gICBcICAgICB8ICBcIHwgIHwgfCAgfCAgfCAgfCAKICAgfCAgICgtLS0tYHwgICwtLS0tJ3wgIHxfKSAgfCAgICB8ICB8IHwgIHxfKSAgfCBgLS0tfCAgfC0tLS1gICAgfCAgLi0tLiAgfHwgIHxfXyAgICAgIHwgIFwgIC8gIHwgICAgLyAgXiAgXCAgICB8ICAgXHwgIHwgfCAgfCAgfCAgfCAKICAgIFwgICBcICAgIHwgIHwgICAgIHwgICAgICAvICAgICB8ICB8IHwgICBfX18vICAgICAgfCAgfCAgICAgICAgfCAgfCAgfCAgfHwgICBfX3wgICAgIHwgIHxcL3wgIHwgICAvICAvX1wgIFwgICB8ICAuIGAgIHwgfCAgfCAgfCAgfCAKLi0tLS0pICAgfCAgIHwgIGAtLS0tLnwgIHxcICBcLS0tLS58ICB8IHwgIHwgICAgICAgICAgfCAgfCAgICAgICAgfCAgJy0tJyAgfHwgIHxfX19fICAgIHwgIHwgIHwgIHwgIC8gIF9fX19fICBcICB8ICB8XCAgIHwgfCAgYC0tJyAgfCAKfF9fX19fX18vICAgICBcX19fX19ffHwgX3wgYC5fX19fX3x8X198IHwgX3wgICAgICAgICAgfF9ffCAgICAgICAgfF9fX19fX18vIHxfX19fX19ffCAgIHxfX3wgIHxfX3wgL19fLyAgICAgXF9fXCB8X198IFxfX3wgIFxfX19fX18vICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA="
RED="\e[31m"
GREEN="\e[32m"
YELLOW="\e[93m"
CYAN="\e[36m"
BGREEN="\e[1;${GREEN}"
BLIGHTYELLOW="\e[1;${YELLOW}"
ITALICRED="\e[3;${RED}"
ENDCOLOR="\e[0m"

# Función para mostrar servicios usando un puerto específico
function list_services() {
    echo -e "$BGREEN Verificando servicios en los puertos necesarios para levantar el docker: ${PORTS[*]}$ENDCOLOR"
    for PORT in "${PORTS[@]}"; do
        PIDS=$(lsof -ti :$PORT 2>/dev/null)
        if [[ -n $PIDS ]]; then
            PORTSTOKILL+=($PORT)
            echo -e "$RED ● El puerto $PORT está en uso por el o los siguientes servicios:$ENDCOLOR"
            for PID in $PIDS; do 
                echo -e "$CYAN  - PID $PID:$ENDCOLOR $(ps -p $PID -o cmd --no-headers)"
            done
        else
            echo -e "$BGREEN ● El puerto $PORT no está en uso. $ENDCOLOR"
        fi
    done
}

# Función para matar los servicios
function kill_processes() {
    list_services
    echo -e "$BGREEN Desea terminar todos los servicios? $ENDCOLOR"
    while [ -z "${KILLALL}" ]; do
    read -p "(y/n): " KILLALL
        case $KILLALL in
            y|Y)
                if [[ -n $KILLALL && $KILLALL =~ ^[yY]+$ ]]; then
                    for PORT in "${PORTSTOKILL[@]}"; do
                        if lsof -i :$PORT 2>/dev/null | awk 'NR!=1 {print $2}' | xargs kill; then
                            echo -e "$BGREEN Servicios del puerto $PORT terminados correctamente $ENDCOLOR"
                        else
                            echo -e "$BRED No se pudieron terminar los servicios del puerto $PORT correctamente\nSaliendo..."
                            exit 0
                        fi
                    done
                fi
            ;;
            n|N)
                echo -e "$BRED Saliendo...$ENDCOLOR"
                exit 0
                ;;
            *)
                echo "Opción inválida, intente nuevamente."
                KILLALL=""
                ;;
        esac
    done
    
}

function building_routine(){
    docker compose up -d --build &&
    docker compose exec app composer install &&
    docker compose exec app composer update &&
    docker compose exec app php artisan migrate &&
    docker compose exec app php artisan key:generate;
    docker compose exec app php artisan config:cache;
    docker compose exec app php artisan migrate;
    docker compose exec -d app php artisan queue:work &&
    echo -e "$BGREEN Worker de email corriendo exitosamente en 2do plano...$ENDCOLOR";
    exit 0;
}

# Menú principal
while true; do
    echo -e "$BLIGHTYELLOW$(echo $BANNER | base64 --decode)$ENDCOLOR"
    echo "----------------------------------------"
    echo -e "El siguiente script es una automatización en bash (la primera tan \"compleja\" hecha por mi) para despejar la cancha antes de levantar el docker y correr los comandos necesarios una vez levantado el docker. c: Espero simplifique la tarea y sea de utilidad <3" 
    echo "----------------------------------------"
    echo -e "$CYAN\nSeleccione una opción:$ENDCOLOR"
    echo "1) Iniciar script completo(list, kill and build)"
    echo "2) Rutina de build (si hay otros contenedores puede no funcionar)"
    echo "3) Salir"
    read -p "opción: " OPTION

    case $OPTION in
        1)
            kill_processes
            building_routine
            ;;
        2)
            building_routine
            ;;
        3)
            echo -e "$BRED Saliendo...$ENDCOLOR"
            exit 0
            ;;
        *)
            echo "Opción inválida, intente nuevamente."
            ;;
    esac
done