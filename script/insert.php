#!/bin/bash


# MQTT BROKER CONFIGURATION (TLS Insecure)

BROKER_MQTT="mqtt.iut-blagnac.fr"
PORT_MQTT="8883"
UTILISATEUR_MQTT="student"
MDP_MQTT="student"
TOPIC_MQTT="sensors/AM107/by-room/+/data"


# MYSQL CONFIGURATION

UTILISATEUR_BDD="sae23"
MDP_BDD="sae23"
NOM_BDD="SAE23"
MYSQL_BIN="/opt/lampp/bin/mysql"

while true
do
  
    # 1. LISTEN TO MQTT BROKER (TLS Insecure mode)

    SORTIE_BRUTE=$(mosquitto_sub -h "$BROKER_MQTT" -p "$PORT_MQTT" -u "$UTILISATEUR_MQTT" -P "$MDP_MQTT" -t "$TOPIC_MQTT" --insecure -v -C 1)

    if [ -n "$SORTIE_BRUTE" ]; then
        TOPIC_RECU=$(echo "$SORTIE_BRUTE" | awk '{print $1}')
        DONNEES_JSON=$(echo "$SORTIE_BRUTE" | cut -d' ' -f2-)
        
        # Extract room name (4th part of the topic)
        SALLE=$(echo "$TOPIC_RECU" | cut -d'/' -f4)


        # 2. PARAMETER 1: TEMPERATURE

        VAL_TEMP=$(echo "$DONNEES_JSON" | jq -r '.[0].temperature')
        if [ "$VAL_TEMP" != "null" ] && [ -n "$VAL_TEMP" ]; then
            NOM_CAPTEUR="AM107_${SALLE}_Temp"
            $MYSQL_BIN -u "$UTILISATEUR_BDD" -D "$NOM_BDD" -e "INSERT IGNORE INTO capteurs (capteur, unite, salle, capt_type) VALUES ('$NOM_CAPTEUR', '°C', '$SALLE', 'temperature');"
            $MYSQL_BIN -u "$UTILISATEUR_BDD" -D "$NOM_BDD" -e "INSERT INTO mesures (date, horaire, valeur, capteur) VALUES (CURDATE(), CURTIME(), $VAL_TEMP, '$NOM_CAPTEUR');"
        fi


        # 3. PARAMETER 2: PRESSURE

        VAL_PRESS=$(echo "$DONNEES_JSON" | jq -r '.[0].pressure')
        if [ "$VAL_PRESS" != "null" ] && [ -n "$VAL_PRESS" ]; then
            NOM_CAPTEUR="AM107_${SALLE}_Press"
            $MYSQL_BIN -u "$UTILISATEUR_BDD" -D "$NOM_BDD" -e "INSERT IGNORE INTO capteurs (capteur, unite, salle, capt_type) VALUES ('$NOM_CAPTEUR', 'hPa', '$SALLE', 'pression');"
            $MYSQL_BIN -u "$UTILISATEUR_BDD" -D "$NOM_BDD" -e "INSERT INTO mesures (date, horaire, valeur, capteur) VALUES (CURDATE(), CURTIME(), $VAL_PRESS, '$NOM_CAPTEUR');"
        fi


        # 4. PARAMETER 3: CO2

        VAL_CO2=$(echo "$DONNEES_JSON" | jq -r '.[0].co2')
        if [ "$VAL_CO2" != "null" ] && [ -n "$VAL_CO2" ]; then
            NOM_CAPTEUR="AM107_${SALLE}_CO2"
            $MYSQL_BIN -u "$UTILISATEUR_BDD" -D "$NOM_BDD" -e "INSERT IGNORE INTO capteurs (capteur, unite, salle, capt_type) VALUES ('$NOM_CAPTEUR', 'ppm', '$SALLE', 'co2');"
            $MYSQL_BIN -u "$UTILISATEUR_BDD" -D "$NOM_BDD" -e "INSERT INTO mesures (date, horaire, valeur, capteur) VALUES (CURDATE(), CURTIME(), $VAL_CO2, '$NOM_CAPTEUR');"
        fi


        # 5. PARAMETER 4: ILLUMINATION

        VAL_LUM=$(echo "$DONNEES_JSON" | jq -r '.[0].illumination')
        if [ "$VAL_LUM" != "null" ] && [ -n "$VAL_LUM" ]; then
            NOM_CAPTEUR="AM107_${SALLE}_Lum"
            $MYSQL_BIN -u "$UTILISATEUR_BDD" -D "$NOM_BDD" -e "INSERT IGNORE INTO capteurs (capteur, unite, salle, capt_type) VALUES ('$NOM_CAPTEUR', 'lux', '$SALLE', 'luminosite');"
            $MYSQL_BIN -u "$UTILISATEUR_BDD" -D "$NOM_BDD" -e "INSERT INTO mesures (date, horaire, valeur, capteur) VALUES (CURDATE(), CURTIME(), $VAL_LUM, '$NOM_CAPTEUR');"
        fi


        # 6. PARAMETER 5: HUMIDITY

        VAL_HUM=$(echo "$DONNEES_JSON" | jq -r '.[0].humidity')
        if [ "$VAL_HUM" != "null" ] && [ -n "$VAL_HUM" ]; then
            NOM_CAPTEUR="AM107_${SALLE}_Hum"
            $MYSQL_BIN -u "$UTILISATEUR_BDD" -D "$NOM_BDD" -e "INSERT IGNORE INTO capteurs (capteur, unite, salle, capt_type) VALUES ('$NOM_CAPTEUR', '%', '$SALLE', 'humidite');"
            $MYSQL_BIN -u "$UTILISATEUR_BDD" -D "$NOM_BDD" -e "INSERT INTO mesures (date, horaire, valeur, capteur) VALUES (CURDATE(), CURTIME(), $VAL_HUM, '$NOM_CAPTEUR');"
        fi

        echo "$(date '+%Y-%m-%d %H:%M:%S') - Données de la salle $SALLE insérées"
    else
        echo "$(date '+%Y-%m-%d %H:%M:%S') - En attente de données..."
    fi

    sleep 15
done
