curl -XPUT 'localhost:9200/_river/leadsfactorydb/_meta' -d '{
    "type" : "jdbc",
    "jdbc" : {
        "url" : "jdbc:mysql://localhost:3306/leadsfactory",
        "user" : "root",
        "password" : "root",
        "sql" : "select * from Leads"
    }
}'
