#!/bin/bash

BROKER_HOST="iot.iut-blagnac.fr"
USERNAME="student"
PASSWORD="student"
PHP_URL="http://localhost/insert.php"

send_value() {
    local nom_cap=$1
    local valeur=$2

    echo "Envoi de $nom_cap avec valeur $valeur..."

    if [[ -n "$valeur" && "$valeur" != "null" ]]; then
        response=$(curl -s -X POST "$PHP_URL" -d "nom_cap=$nom_cap&valeur_mesure=$valeur")
        echo "Réponse serveur: $response"
    else
        echo "Valeur non valide, skip."
    fi
}

while true; do
	
	echo ""
	echo "==> [$(date '+%d/%m/%Y - %Hh%M')] <=="
	echo ""
    echo "=== Récupération de données ==="

    TEMP_E101=$(mosquitto_sub -h "$BROKER_HOST" -u "$USERNAME" -P "$PASSWORD" -t "iut/bate/etage1/E101/temperature" -C 1 | jq -r '.value')
    echo "Température E101 récupérée: $TEMP_E101"
    HUM_E101=$(mosquitto_sub -h "$BROKER_HOST" -u "$USERNAME" -P "$PASSWORD" -t "iut/bate/etage1/E101/humidite" -C 1 | jq -r '.value')
    echo "Humidité E101 récupérée: $HUM_E101"
    HUM_E102=$(mosquitto_sub -h "$BROKER_HOST" -u "$USERNAME" -P "$PASSWORD" -t "iut/bate/etage1/E102/humidite" -C 1 | jq -r '.value')
    echo "Humidité E102 récupérée: $HUM_E102"
    LUM_E102=$(mosquitto_sub -h "$BROKER_HOST" -u "$USERNAME" -P "$PASSWORD" -t "iut/bate/etage1/E102/luminosite" -C 1 | jq -r '.value')
    echo "Luminosité E102 récupérée: $LUM_E102"
    LUM_E207=$(mosquitto_sub -h "$BROKER_HOST" -u "$USERNAME" -P "$PASSWORD" -t "iut/bate/etage2/E207/luminosite" -C 1 | jq -r '.value')
    echo "Luminosité E207 récupérée: $LUM_E207"
    CO2_E207=$(mosquitto_sub -h "$BROKER_HOST" -u "$USERNAME" -P "$PASSWORD" -t "iut/bate/etage2/E207/co2" -C 1 | jq -r '.value')
    echo "CO2 E207 récupéré: $CO2_E207"
    TEMP_E208=$(mosquitto_sub -h "$BROKER_HOST" -u "$USERNAME" -P "$PASSWORD" -t "iut/bate/etage2/E208/temperature" -C 1 | jq -r '.value')
    echo "Température E208 récupérée: $TEMP_E208"
    CO2_E208=$(mosquitto_sub -h "$BROKER_HOST" -u "$USERNAME" -P "$PASSWORD" -t "iut/bate/etage2/E208/co2" -C 1 | jq -r '.value')
    echo "CO2 E208 récupéré: $CO2_E208"

    echo ""

    send_value "E101_temperature" "$TEMP_E101"
    send_value "E101_humidite" "$HUM_E101"
    send_value "E102_humidite" "$HUM_E102"
    send_value "E102_luminosite" "$LUM_E102"
    send_value "E207_luminosite" "$LUM_E207"
    send_value "E207_co2" "$CO2_E207"
    send_value "E208_temperature" "$TEMP_E208"
    send_value "E208_co2" "$CO2_E208"

    echo "=== Cycle terminé, pause 120s ==="
    echo ""
    sleep 120
done
