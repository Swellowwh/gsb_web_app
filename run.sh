#!/bin/bash

# Définir les variables
PHP_PORT=8000
VITE_PORT=5173
PHP_DIR="."  # Répertoire pour le serveur PHP, modifiez selon votre projet
VITE_DIR="./gsb-app"  # Répertoire pour l'application Vite.js

# Couleurs pour la sortie
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour vérifier si un port est disponible
port_is_available() {
  if command -v lsof > /dev/null; then
    if lsof -i:$1 > /dev/null; then
      return 1
    else
      return 0
    fi
  elif command -v netstat > /dev/null; then
    if netstat -tuln | grep ":$1 " > /dev/null; then
      return 1
    else
      return 0
    fi
  else
    # Si aucun outil n'est disponible, on suppose que le port est disponible
    return 0
  fi
}

# Vérifier si les ports sont disponibles
if ! port_is_available $PHP_PORT; then
  echo -e "${RED}Le port $PHP_PORT est déjà utilisé. Veuillez choisir un autre port pour PHP.${NC}"
  exit 1
fi

if ! port_is_available $VITE_PORT; then
  echo -e "${RED}Le port $VITE_PORT est déjà utilisé. Veuillez choisir un autre port pour Vite.${NC}"
  exit 1
fi

# Démarrer le serveur PHP dans un terminal/processus séparé
echo -e "${GREEN}Démarrage du serveur PHP sur le port $PHP_PORT...${NC}"
cd "$PHP_DIR" && php -S localhost:$PHP_PORT &
PHP_PID=$!

# Attendre que le serveur PHP soit prêt
sleep 2

# Démarrer npm run dev dans un autre terminal/processus
echo -e "${GREEN}Démarrage de npm run dev sur le port $VITE_PORT...${NC}"
cd "$VITE_DIR" && npm run dev -- --host &
NPM_PID=$!

# Attendre que npm run dev soit prêt
sleep 5

# Afficher les liens
echo ""
echo -e "${BLUE}=== Serveurs démarrés avec succès ===${NC}"
echo -e "${GREEN}Serveur PHP:${NC} http://localhost:$PHP_PORT"
echo -e "${GREEN}Serveur Vite:${NC} http://localhost:$VITE_PORT (également accessible via votre IP réseau)
echo ""
echo -e "${BLUE}=== Pour arrêter les serveurs, appuyez sur Ctrl+C ===${NC}"

# Gérer l'arrêt propre des processus
cleanup() {
  echo ""
  echo -e "${GREEN}Arrêt des serveurs...${NC}"
  kill $PHP_PID 2>/dev/null
  kill $NPM_PID 2>/dev/null
  echo -e "${GREEN}Serveurs arrêtés.${NC}"
  exit 0
}

trap cleanup INT TERM

# Maintenir le script en exécution
wait