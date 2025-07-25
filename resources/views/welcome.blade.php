<!-- resources/views/message.blade.php -->

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Enviar Mensagem</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">

<div class="container">
    <h1 class="mb-4">Enviar Mensagem</h1>

    <div class="mb-3">
        <input type="text" id="mensagem" class="form-control" placeholder="Digite sua mensagem" required="required">
    </div>

    <div class="mb-3">
        <button id="enviar" class="btn btn-primary">Enviar</button>
    </div>

    <div>
        <label>Status:</label>
        <p id="statusLabel" class="fw-bold text-success">Aguardando envio...</p>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function () {
        let pollingInterval = null;

        $('#enviar').click(function () {
            const mensagem = $('#mensagem').val();
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            if (!mensagem) {
                alert('Digite uma mensagem');
                return;
            }

            $('#statusLabel').text('Enviando...');

            $.ajax({
                url: '/public/api/message',
                type: 'POST',
                data: { message: mensagem },
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function (response) {
                    $('#statusLabel').text('Mensagem enviada. Aguardando status...');

                    const messageId = response.id;

                    if (pollingInterval) {
                        clearInterval(pollingInterval);
                    }

                    pollingInterval = setInterval(function () {
                        $.getJSON(`/public/api/message/${messageId}`, function (data) {
                            $('#statusLabel').text('Status: ' + JSON.stringify(data));
                        });
                    }, 5000);
                },
                error: function () {
                    $('#statusLabel').text('Erro ao enviar mensagem');
                }
            });
        });
    });
</script>
</body>
</html>
