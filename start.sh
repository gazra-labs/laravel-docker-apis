GREEN='\e[32m'
RED='\e[31m'
YELLOW='\e[33m'
NC='\033[0m'

# Detecting OS because most of the users use gitbash in windows and
# that needs to add "winpty" prefix for every docker command
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo -e "${YELLOW}---------------------------Detecting Operating System -----------------------------${NC}"
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
if [ "$(uname)" == "Darwin" ]; then
    echo "Mac OS X detected" # Do something under Mac OS X platform    
    CMD_PREFIX=""    
    YELLOW=''
    NC=''
elif [ "$(expr substr $(uname -s) 1 5)" == "Linux" ]; then
    echo "GNU/Linux detected" # Do something under GNU/Linux platform
    CMD_PREFIX=""
elif [ "$(expr substr $(uname -s) 1 10)" == "MINGW32_NT" ]; then
    echo "32 bits Windows NT detected" # Do something under 32 bits Windows NT platform
    CMD_PREFIX='winpty '
elif [ "$(expr substr $(uname -s) 1 10)" == "MINGW64_NT" ]; then
    echo "64 bits Windows NT detected" # Do something under 64 bits Windows NT platform
    CMD_PREFIX='winpty '
fi
# echo "${CMD_PREFIX}should be added"
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo "";



echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo -e "${YELLOW}-----------Copying .env file, please add your Google API Key in this file----------${NC}"
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
FILE=.env
if [ -f "$FILE" ]; then
    echo "File ${FILE} already exist"
else 
    cp .env.example .env
    echo "File ${FILE} Copied successfully."
fi
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo "";


echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo -e "${YELLOW}--Please enter your Google API Key and it will automatically set in .env file------${NC}"
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
read -p 'Your Google API Key: ' googleApiKey
if [ "${googleApiKey}" = '' ]; then
    echo "Google API Key is empty."
else 
    sed -i "s/GOOGLE_API_KEY=.*/GOOGLE_API_KEY=${googleApiKey}/g" ".env"
    echo "Your Google API Key is setup into .env file."
fi
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo "";



echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo -e "${YELLOW}-----------Shutting down previous docker, Docker Build and starting docker---------${NC}"
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
${CMD_PREFIX} docker-compose down -v && docker-compose build --no-cache && docker-compose up -d
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo "";


echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo -e "${YELLOW}-------------------------------Installing Composer---------------------------------${NC}"
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
${CMD_PREFIX} docker-compose exec laravel-app composer install
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo "";


echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo -e "${YELLOW}-------------------------------Generating App Key--------------------------------- ${NC}"
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
${CMD_PREFIX} docker-compose exec laravel-app php artisan key:generate
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo "";

echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo -e "${YELLOW}--------------------------Migrating database with seeds----------------------------${NC}"
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
${CMD_PREFIX} docker-compose exec laravel-app php artisan migrate --seed
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo "";

echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo -e "${YELLOW}-----------------------------Generating API Document-------------------------------${NC}"
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
${CMD_PREFIX} docker-compose exec laravel-app php artisan l5-swagger:generate
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo "";

echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo -e "${YELLOW}--------------------------Your working API URL and Port----------------------------${NC}"
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
${CMD_PREFIX} docker ps -a
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo "";


echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo -e "${YELLOW}----------------------------Performing PHPUnit Tests-------------------------------${NC}"
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
sleep 120
${CMD_PREFIX} docker exec laravel-app ./vendor/bin/phpunit
echo -e "${YELLOW}-----------------------------------------------------------------------------------${NC}"
echo "";
