<?php
session_start();
include("config.php");

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cpf = trim($_POST['cpf']);
    $senha = $_POST['senha'];

    if (empty($cpf) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos!";
    } else {
        // Remove formatação do CPF para buscar no banco
        $cpf_limpo = preg_replace('/[^0-9]/', '', $cpf);

        $stmt = $conn->prepare("SELECT * FROM prestadores WHERE cpf = ?");
        $stmt->bind_param("s", $cpf_limpo);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {

            if (password_verify($senha, $row['senha'])) {

                $_SESSION['prestador_id'] = $row['id'];
                $_SESSION['prestador_nome'] = $row['nome'];
                $_SESSION['prestador_cpf'] = $row['cpf'];
                $_SESSION['prestador_descricao'] = $row['descricao'];
                $_SESSION['tipo'] = 'prestador';

                header("Location: dashboard_prestador.php");
                exit;

            } else {
                $erro = "Senha incorreta!";
            }

        } else {
            $erro = "CPF não encontrado!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Prestador de Serviços</title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    :root {
        --cor-principal: #111111;
        --cor-secundaria: #f5f5f5;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: var(--cor-secundaria);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
        color: var(--cor-principal);
    }

    .container {
        background: white;
        border-radius: 14px;
        border: 1px solid #e5e5e5;
        overflow: hidden;
        width: 100%;
        max-width: 430px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .header {
        padding: 35px 30px 25px;
        text-align: center;
        border-bottom: 1px solid #ececec;
        background: white;
    }

    .header h1 {
        font-size: 30px;
        font-weight: 700;
        letter-spacing: -1px;
        color: var(--cor-principal);
        margin-bottom: 8px;
    }

    .header p {
        font-size: 14px;
        color: #666;
    }

    .content {
        padding: 35px 30px;
    }

    .info-card {
        background: #fafafa;
        border: 1px solid #e5e5e5;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 22px;
        text-align: center;
    }

    .info-card p {
        font-size: 13px;
        color: #666;
        line-height: 1.5;
    }

    .form-group {
        margin-bottom: 22px;
        position: relative;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 600;
        color: var(--cor-principal);
    }

    .form-group input {
        width: 100%;
        padding: 14px;
        border: 1px solid #dcdcdc;
        border-radius: 10px;
        background: #fafafa;
        font-size: 14px;
        outline: none;
        transition: 0.2s ease;
        font-family: inherit;
        color: var(--cor-principal);
    }

    .form-group input:focus {
        border-color: var(--cor-principal);
        background: white;
    }

    .form-group input::placeholder {
        color: #999;
    }

    .btn-login {
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 10px;
        background: var(--cor-principal);
        color: white;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .btn-login:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .register-link {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #ececec;
        text-align: center;
    }

    .register-link a,
    .back-link a {
        text-decoration: none;
        color: var(--cor-principal);
        font-size: 14px;
        font-weight: 600;
        transition: 0.2s ease;
    }

    .register-link a:hover,
    .back-link a:hover {
        opacity: 0.7;
    }

    .back-link {
        margin-top: 14px;
        text-align: center;
    }

    .mensagem-erro {
        padding: 14px;
        border-radius: 10px;
        margin-bottom: 20px;
        text-align: center;
        background: var(--cor-principal);
        color: white;
        font-size: 14px;
        animation: fadeIn 0.2s ease;
    }

    .icone-input {
        position: absolute;
        right: 15px;
        top: 42px;
        cursor: pointer;
        color: #777;
        transition: 0.2s ease;
        user-select: none;
        font-size: 13px;
        font-weight: 600;
    }

    .icone-input:hover {
        color: var(--cor-principal);
    }

    @media (max-width: 480px) {
        .content {
            padding: 25px 20px;
        }

        .header {
            padding: 28px 20px;
        }

        .header h1 {
            font-size: 25px;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Login Prestador</h1>
            <p>Acesse sua conta para gerenciar seus serviços</p>
        </div>
        
        <div class="content">
            <?php if ($erro): ?>
                <div class="mensagem-erro">
                    <span class="icone"></span> <?php echo $erro; ?>
                </div>
            <?php endif; ?>
            
            <div class="info-card">
                <p>Ofereça seus serviços para condomínios<br>
                <span class="destaque">✓</span> Gerencie suas solicitações<br>
                <span class="destaque">✓</span> Acompanhe seus atendimentos</p>
            </div>
            
            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label>CPF</label>
                    <input type="text" 
                           name="cpf" 
                           id="cpf"
                           placeholder="Digite seu CPF" 
                           value="<?php echo isset($_POST['cpf']) ? htmlspecialchars($_POST['cpf']) : ''; ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label> Senha</label>
                    <input type="password" 
                           name="senha" 
                           id="senha"
                           placeholder="Digite sua senha"
                           required>
                    <span class="icone-input" onclick="toggleSenha()">
                        
                    </span>
                </div>
                
                <button type="submit" class="btn-login"> Entrar</button>
            </form>
            
            <div class="register-link">
                <a href="cadastro_prestador.php">Não tem conta? Cadastre-se aqui</a>
            </div>
            
            <div class="back-link">
                <a href="index.php">← Voltar para página inicial</a>
            </div>
        </div>
    </div>
    
    <script>
        // Máscara para CPF
        const cpfInput = document.getElementById('cpf');
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                if (value.length > 9) {
                    value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
                } else if (value.length > 6) {
                    value = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3');
                } else if (value.length > 3) {
                    value = value.replace(/(\d{3})(\d{3})/, '$1.$2');
                } else if (value.length > 0) {
                    value = value.replace(/(\d{3})/, '$1');
                }
                e.target.value = value;
            }
        });
        
        // Função para mostrar/esconder senha
        function toggleSenha() {
            const senhaInput = document.getElementById('senha');
            const icon = document.querySelector('.icone-input');
            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                icon.textContent = '🙈';
            } else {
                senhaInput.type = 'password';
                icon.textContent = '👁️';
            }
        }
        
        // Validação antes de enviar
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const cpf = document.getElementById('cpf').value.trim();
            const senha = document.getElementById('senha').value;
            
            if (!cpf || !senha) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos!');
                return false;
            }
            
            const cpfNumeros = cpf.replace(/\D/g, '');
            if (cpfNumeros.length !== 11) {
                e.preventDefault();
                alert('CPF inválido! Deve conter 11 dígitos.');
                return false;
            }
            
            if (senha.length < 4) {
                e.preventDefault();
                alert('A senha deve ter no mínimo 4 caracteres!');
                return false;
            }
        });
        
        // Adicionar efeito de enter para enviar
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('loginForm').submit();
                }
            });
        });
        
        // Foco automático no primeiro campo
        cpfInput.focus();
        
        // Adicionar efeito de digitação
        cpfInput.addEventListener('keyup', function() {
            if (this.value.replace(/\D/g, '').length === 11) {
                this.style.borderColor = '#28a745';
            } else {
                this.style.borderColor = '#e0e0e0';
            }
        });
    </script>
</body>
</html>