INTRODUCTION
------------
 
 Provides a rest API service 

REQUIRED MODULES
----------------
- REST UI
- KEY_AUTH

INSTALLATION
------------
- Install and activate the modules REST UI and KEY AUTH
- Install and activate module ADT REST API

- Api key setting :
    /en/admin/config/services/key-auth
    - Key length : 64
    - Parameter name: api-key
    - Detection methods: header

- Rest Resource Setting :
    /en/admin/config/services/rest
    - Enable "ADT Lead Rest API (/api/v1.0/rest/lead)" Resource
    - Set the below :
        Granularity:
            Resource
        Methods: 
            POST
        Accepted request formats:
            json
        Authentication providers:
            key_auth  
- Create a Key
    - Create a CMS user and set admin role. (ie. rest_api_user)
    - Edit the user and Set the key on the Key Authentication tab, ie: 
    api-key: c4c0f423c5c19d3deceb55ca8c21cfa22aabc0643c1ae90f07ea352cc3118b

- How to consume the API call?
URL: 
https://**server**/api/v1.0/user/login
METHOD: 
POST
HEADER:
api-key: **API key generated**
BODY:
RAW JSON
ie.
{
    "username": "mianpino@gmail.com",
    "password": "Xy123456"
}





 



