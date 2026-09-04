#!/bin/bash
#
#  Respaldo de AQUALIVE: la base de datos y las fotos de los tickets.
#  Lo lanza el cron de Hostinger una vez al dia. Guarda los ultimos DIAS
#  y borra los mas viejos, para que no crezca sin control.
#
#  A mano:  ~/aqualive/scripts/respaldar.sh
#

set -u

PROYECTO="$HOME/aqualive"
DESTINO="$HOME/respaldos"
DIAS=14

FECHA=$(date +%Y%m%d-%H%M)
REGISTRO="$DESTINO/respaldo.log"

mkdir -p "$DESTINO"
cd "$PROYECTO" || { echo "$(date '+%F %T') FALLO: no esta $PROYECTO" >> "$REGISTRO"; exit 1; }

#  Lee del .env quitando comillas y el retorno de carro que a veces deja
#  un editor de Windows
leer() {
    grep "^$1=" .env | head -1 | sed "s/^$1=//; s/\r\$//; s/^\"//; s/\"\$//; s/^'//; s/'\$//"
}

BASE=$(leer DB_DATABASE)
USUARIO=$(leer DB_USERNAME)
CLAVE=$(leer DB_PASSWORD)

if [ -z "$BASE" ] || [ -z "$USUARIO" ]; then
    echo "$(date '+%F %T') FALLO: no pude leer los datos de conexion del .env" >> "$REGISTRO"
    exit 1
fi

#  La contraseña va en un archivo temporal y no en la linea de comandos:
#  esto es un servidor compartido y "ps" la dejaria a la vista de todos.
CONF=$(mktemp)
chmod 600 "$CONF"
printf '[client]\nuser=%s\npassword=%s\n' "$USUARIO" "$CLAVE" > "$CONF"

mysqldump --defaults-extra-file="$CONF" --single-transaction --quick "$BASE" \
    2>> "$REGISTRO" | gzip > "$DESTINO/base-$FECHA.sql.gz"
ESTADO=${PIPESTATUS[0]}

rm -f "$CONF"

if [ "$ESTADO" -ne 0 ]; then
    echo "$(date '+%F %T') FALLO: el volcado de la base termino en $ESTADO" >> "$REGISTRO"
    rm -f "$DESTINO/base-$FECHA.sql.gz"
    exit 1
fi

#  Las fotos de los tickets viven en storage, no en la base: sin ellas el
#  respaldo restauraria los tickets sin sus fotos.
tar -czf "$DESTINO/fotos-$FECHA.tar.gz" -C "$PROYECTO" storage/app/private 2>> "$REGISTRO"

#  Fuera los viejos
find "$DESTINO" -maxdepth 1 -name 'base-*.sql.gz'  -mtime +$DIAS -delete
find "$DESTINO" -maxdepth 1 -name 'fotos-*.tar.gz' -mtime +$DIAS -delete

PESO_BASE=$(du -h "$DESTINO/base-$FECHA.sql.gz" | cut -f1)
PESO_FOTOS=$(du -h "$DESTINO/fotos-$FECHA.tar.gz" 2>/dev/null | cut -f1)
GUARDADOS=$(find "$DESTINO" -maxdepth 1 -name 'base-*.sql.gz' | wc -l)

echo "$(date '+%F %T') ok  base=$PESO_BASE  fotos=${PESO_FOTOS:-0}  guardados=$GUARDADOS" >> "$REGISTRO"
