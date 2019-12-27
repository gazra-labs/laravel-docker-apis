# Docker-laravel-apis
An simple APIs with Shell Script, Docker and Laravel

> :warning::warning: Docker version should be >18.00 :warning::warning:

Follow these simple steps to working API project
1. Clone the repository 
   
   `mkdir docker-laravel-api`
   
   `cd docker-laravel-api`
   
   `git clone https://github.com/gazra-labs/docker-laravel-apis.git . `
    
2. Run `start.sh` file, the file will execure following commands
    
    Above start.sh file will run following commands 
    
    a. Copy .env file 
    
    `cp .env.example .env`
    
      >You can enter your Google API Key in .env file. 
    
    b. Start Docker and making build
    
    `docker-compose down -v && docker-compose build && docker-compose up -d`

    c. To install composer in the directory
    
    `docker-compose exec laravel-app composer install`

    d. To migrate database into docker
    
    `docker-compose exec laravel-app php artisan key:generate`
    
    `docker-compose exec laravel-app php artisan migrate --seed`
	
    e. Generating API Document
    
    `docker-compose exec laravel-app php artisan l5-swagger:generate`

    f. To check working directory URL
    
    `docker ps -a`
	
    g. Performing PHPUnit Tests 
    
    `sleep 150`
    
    `docker exec laravel-app ./vendor/bin/phpunit`

5. Hit the URL and you will find the working project. Working URL would be http://0.0.0.0:8080 



## API Uses and Documentation
There are 3 APIs with different methods. To list, create and update order

### Basic details of APIs are 

Parameters | Values | Remarks
--- | --- | ---
URL | `0.0.0.0:8080` | Please check `docker ps -a` to confirm current working url
API Path | {URL} | 
End Point | {URL}/orders?page=:page&limit=:limit | Example of list orders
Example Full URI | http://0.0.0.0:8080/api/orders?page=:page&limit=:limit | 
API Documentation | {URL}/api/documentation | 
Code Coverage | {URL}/CodeCoverage/ | 




1. **List Order API:** 

	Name | Values
	--- | ---
	End Point | {URL}/orders?page=:page&limit=:limit 
	Method | GET 
	Requested JSON | No JSON Body 
	
	**Response JSON**
	
	```
	[
	    {
	        "id": 1,
	        "distance": 10636,
	        "status": "UNASSIGNED"
	    },
	    {
	        "id": 2,
	        "distance": 13634,
	        "status": "UNASSIGNED"
	    },
	    ...
	]
	```


2. **Create Order:** 
	
	Name | Values
	--- | ---
	End Point | {URL}/orders  
	Method | POST 
	
	**Requested JSON**
	
	```
	{
	    "origin": ["25.3260412","77.4978736"],
	    "destination": ["25.127270", "76.855173"]
	}
	```
	
	**Response JSON:** 
	```
	{
	    "id": 51,
	    "distance": 12378.83,
	    "status": "UNASSIGNED"
	}
	```


3. **Update order:** 

	Name | Values
	--- | ---
	End Point | {URL}/orders/{id}  
	Method | PATCH 
	
	**Requested JSON**
	
	```
	{
	    "status": "TAKEN"
	}
	```
	
	**Response JSON**
	```
	{
	    "status": "SUCCESS"
	}
	```


### Some useful commands for working directory and dockers
To test PHP Unit testing

`docker exec laravel-app ./vendor/bin/phpunit`

To work under the Container with devuser... 

`docker exec -it laravel-app bash -c "sudo -u devuser /bin/bash"`

To work under mysql server in the container with devuser.. (Password = 123456)

`docker exec -it mysql-db bash -c "mysql -u admin -psecret -p"` 
    
If build not works please try these commands

To remove and refresh all docker system      `docker system prune -a`

To stop all working and unsed containers        `docker stop $(docker ps -a -q)`

To remove all working and unsed containers      `docker rm $(docker ps -a -q)` 

To build image locally, start build and using no cache args `docker-compose build --no-cache && docker-compose up -d && docker-compose logs -f`

