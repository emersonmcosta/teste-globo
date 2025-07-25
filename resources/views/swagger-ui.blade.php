
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Message API - Swagger</title>
  <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist/swagger-ui.css" />
</head>
<body>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.com/swagger-ui-dist/swagger-ui-bundle.js"></script>
  <script>
    window.onload = function() {
      const ui = SwaggerUIBundle({
        spec: {"openapi": "3.0.0", "info": {"title": "Message API", "version": "1.0.0", "description": "API para criar, listar, exibir e reprocessar mensagens usando Laravel + Queue."}, "servers": [{"url": "http://localhost/api", "description": "Servidor local"}], "paths": {"/messages": {"get": {"summary": "Listar todas as mensagens", "tags": ["Messages"], "responses": {"200": {"description": "Lista de mensagens", "content": {"application/json": {"schema": {"type": "array", "items": {"$ref": "#/components/schemas/Message"}}}}}}}, "post": {"summary": "Criar uma nova mensagem", "tags": ["Messages"], "requestBody": {"required": true, "content": {"application/json": {"schema": {"type": "object", "required": ["message"], "properties": {"message": {"type": "string", "example": "Ol\u00e1, mundo"}}}}}}, "responses": {"200": {"description": "Mensagem criada com sucesso", "content": {"application/json": {"schema": {"type": "object", "properties": {"id": {"type": "integer"}, "message": {"type": "string"}}}}}}, "422": {"description": "Dados inv\u00e1lidos"}, "500": {"description": "Erro interno do servidor"}}}}, "/messages/{id}": {"get": {"summary": "Exibir uma mensagem pelo ID", "tags": ["Messages"], "parameters": [{"name": "id", "in": "path", "required": true, "schema": {"type": "integer"}}], "responses": {"200": {"description": "Dados da mensagem", "content": {"application/json": {"schema": {"$ref": "#/components/schemas/Message"}}}}, "404": {"description": "Mensagem n\u00e3o encontrada"}}}}, "/messages/{id}/reprocess": {"post": {"summary": "Reprocessar uma mensagem pelo ID", "tags": ["Messages"], "parameters": [{"name": "id", "in": "path", "required": true, "schema": {"type": "integer"}}], "responses": {"200": {"description": "Mensagem reprocessada com sucesso"}, "404": {"description": "Mensagem n\u00e3o encontrada"}, "422": {"description": "N\u00e3o \u00e9 poss\u00edvel reprocessar uma mensagem j\u00e1 enviada"}}}}}, "components": {"schemas": {"Message": {"type": "object", "properties": {"id": {"type": "integer", "example": 1}, "message": {"type": "string", "example": "Ol\u00e1, mundo"}, "status": {"type": "string", "example": "pending"}, "retries": {"type": "integer", "example": 0}, "created_at": {"type": "string", "format": "date-time"}, "updated_at": {"type": "string", "format": "date-time"}}}}}},
        dom_id: '#swagger-ui',
        deepLinking: true,
      });
    };
  </script>
</body>
</html>
