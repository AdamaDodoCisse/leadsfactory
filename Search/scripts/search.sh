curl -XPOST "http://localhost:9200/leadsfactory/_search" -d '{
    "query": {
        "match": {
            "content.test": "toto"
        }
    }
}'
