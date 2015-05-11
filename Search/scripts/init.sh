curl -XPUT '127.0.0.1:9200/_river/my_jdbc_river/_meta' -d '{
    "type" : "jdbc",
    "jdbc" : {
        "url" : "jdbc:mysql://localhost:3306/leadsfactory",
        "user" : "root",
        "password" : "root",
        "index" : "leadsfactory",
        "sql" : "select * from Leads"
    }
}'
