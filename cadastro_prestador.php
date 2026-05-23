<?php
include("config.php");

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validação básica
    if (empty($_POST['nome']) || empty($_POST['cpf']) || empty($_POST['descricao']) || 
        empty($_POST['preco']) || empty($_POST['senha']) || empty($_POST['regioes'])) {
        $erro = "Todos os campos são obrigatórios!";
    } else {
        
        $nome = trim($_POST['nome']);
        $cpf = trim($_POST['cpf']);
        $descricao = trim($_POST['descricao']);
        $preco = floatval($_POST['preco']);
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

                $cpf = trim($_POST['cpf']);
$cpf = preg_replace('/[^0-9]/', '', $cpf); 
        
        // Verificar se CPF já existe
        $check_stmt = $conn->prepare("SELECT id FROM prestadores WHERE cpf = ?");
        $check_stmt->bind_param("s", $cpf);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $erro = "CPF já cadastrado!";
        } else {
            // 🔧 cria prestador (SEM condominio_id)
            $stmt = $conn->prepare("
                INSERT INTO prestadores (nome, cpf, descricao, preco_medio, senha)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssds", $nome, $cpf, $descricao, $preco, $senha);
            
            if ($stmt->execute()) {
                $prestador_id = $conn->insert_id;
                
                // 🔥 salva regiões
                $regioes_salvas = 0;
                foreach ($_POST['regioes'] as $regiao) {
                    $regiao = trim($regiao);
                    if (!empty($regiao)) {
                        $stmt_reg = $conn->prepare("
                            INSERT INTO prestador_regioes (prestador_id, regiao)
                            VALUES (?, ?)
                        ");
                        $stmt_reg->bind_param("is", $prestador_id, $regiao);
                        if ($stmt_reg->execute()) {
                            $regioes_salvas++;
                        }
                        $stmt_reg->close();
                    }
                }
                
                if ($regioes_salvas > 0) {
                    $mensagem = "Prestador cadastrado com sucesso!";
                    // Redirecionar para página de login após 2 segundos
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'login_prestador.php';
                        }, 2000);
                    </script>";
                } else {
                    $erro = "Erro ao salvar regiões!";
                }
            } else {
                $erro = "Erro ao cadastrar: " . $conn->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Prestador de Serviços</title>
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
        max-width: 600px;
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
        font-size: 28px;
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

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 600;
        color: var(--cor-principal);
    }

    .form-group input,
    .form-group textarea {
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

    .form-group input:focus,
    .form-group textarea:focus {
        border-color: var(--cor-principal);
        background: white;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 90px;
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: #999;
    }

    .regioes-container {
        background: #fafafa;
        border: 1px solid #e5e5e5;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .regioes-container h3 {
        font-size: 15px;
        color: var(--cor-principal);
        margin-bottom: 15px;
        font-weight: 600;
    }

    .regioes-container small {
        display: block;
        margin-top: 4px;
        color: #777;
        font-size: 12px;
    }

    .regiao-item {
        display: flex;
        gap: 10px;
        margin-bottom: 12px;
    }

    .regiao-item input {
        flex: 1;
        padding: 12px;
        border: 1px solid #dcdcdc;
        border-radius: 10px;
        background: white;
        font-size: 14px;
        outline: none;
        transition: 0.2s ease;
    }

    .regiao-item input:focus {
        border-color: var(--cor-principal);
    }

    .btn-remover {
        border: none;
        background: var(--cor-principal);
        color: white;
        padding: 0 14px;
        border-radius: 10px;
        cursor: pointer;
        transition: 0.2s ease;
        font-size: 13px;
    }

    .btn-remover:hover {
        opacity: 0.85;
    }

    .btn-adicionar {
        width: 100%;
        padding: 13px;
        border: 1px solid #dcdcdc;
        border-radius: 10px;
        background: white;
        color: var(--cor-principal);
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s ease;
        margin-top: 5px;
    }

    .btn-adicionar:hover {
        background: var(--cor-principal);
        color: white;
    }

    .btn-cadastrar {
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
        margin-top: 10px;
    }

    .btn-cadastrar:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .login-link {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #ececec;
        text-align: center;
    }

    .login-link a {
        text-decoration: none;
        color: var(--cor-principal);
        font-size: 14px;
        font-weight: 600;
        transition: 0.2s ease;
    }

    .login-link a:hover {
        opacity: 0.7;
    }

    .mensagem {
        padding: 14px;
        border-radius: 10px;
        margin-bottom: 20px;
        text-align: center;
        font-size: 14px;
    }

    .mensagem.sucesso {
        background: #f3f3f3;
        color: var(--cor-principal);
        border: 1px solid #dcdcdc;
    }

    .mensagem.erro {
        background: var(--cor-principal);
        color: white;
    }

    .icone {
        margin-right: 5px;
    }

    @media (max-width: 480px) {
        .content {
            padding: 25px 20px;
        }

        .header {
            padding: 28px 20px;
        }

        .header h1 {
            font-size: 24px;
        }

        .regiao-item {
            flex-direction: column;
        }

        .btn-remover {
            width: 100%;
            padding: 12px;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cadastro de Prestador</h1>
            <p>Ofereça seus serviços para condomínios</p>
        </div>
        
        <div class="content">
            <?php if ($mensagem): ?>
                <div class="mensagem sucesso">
                    <span class="icone"></span> <?php echo $mensagem; ?>
                    <div style="font-size: 12px; margin-top: 5px;">Redirecionando para o login...</div>
                </div>
            <?php endif; ?>
            
            <?php if ($erro): ?>
                <div class="mensagem erro">
                    <span class="icone"></span> <?php echo $erro; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="cadastroForm">
                <div class="form-group">
                    <label> Nome do Prestador</label>
                    <input type="text" name="nome" placeholder="Digite seu nome ou nome da empresa" 
                           value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>CPF</label>
                    <input type="text" name="cpf" placeholder="Digite seu CPF" 
                           value="<?php echo isset($_POST['cpf']) ? htmlspecialchars($_POST['cpf']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Tipo de Serviço</label>
                    <textarea name="descricao" placeholder="Ex: Encanador, Eletricista, Pintor, Limpeza, etc." required><?php echo isset($_POST['descricao']) ? htmlspecialchars($_POST['descricao']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Preço Médio</label>
                    <input type="number" step="0.01" name="preco" placeholder="Ex: 150.00" 
                           value="<?php echo isset($_POST['preco']) ? htmlspecialchars($_POST['preco']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="senha" placeholder="Digite sua senha" required>
                </div>
                
                <div class="regioes-container">
                    <h3>
                        <span></span> 
                        Regiões que atende
                        <small style="font-size: 12px; color: #666;">(adicione quantas precisar)</small>
                    </h3>
                    <div id="regioes-wrapper">
                        <?php 
                        $regioes = isset($_POST['regioes']) ? $_POST['regioes'] : ['', '', ''];
                        foreach ($regioes as $index => $regiao):
                        ?>
                        <div class="regiao-item">
                            <input type="text" name="regioes[]" placeholder="Ex: Sorocaba/SP" 
                                   value="<?php echo htmlspecialchars($regiao); ?>">
                            <?php if ($index >= 3): ?>
                            <button type="button" class="btn-remover" onclick="this.parentElement.remove()">Remover</button>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn-adicionar" onclick="adicionarRegiao()">
                        + Adicionar região
                    </button>
                </div>
                
                <button type="submit" class="btn-cadastrar"> Cadastrar Prestador</button>
            </form>
            
            <div class="login-link">
                <a href="login_prestador.php"> Já tem cadastro? Faça login aqui</a>
            </div>
        </div>
    </div>
    
    <script>
        // Máscara para CPF
        document.querySelector('input[name="cpf"]').addEventListener('input', function(e) {
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
        
        // Função para adicionar nova região
        function adicionarRegiao() {
            const wrapper = document.getElementById('regioes-wrapper');
            const div = document.createElement('div');
            div.className = 'regiao-item';
            div.innerHTML = `
                <input type="text" name="regioes[]" placeholder="Ex: Nova região/UF">
                <button type="button" class="btn-remover" onclick="this.parentElement.remove()">Remover</button>
            `;
            wrapper.appendChild(div);
        }
        
        // Validação antes de enviar
        document.getElementById('cadastroForm').addEventListener('submit', function(e) {
            const nome = document.querySelector('input[name="nome"]').value.trim();
            const cpf = document.querySelector('input[name="cpf"]').value.trim();
            const descricao = document.querySelector('textarea[name="descricao"]').value.trim();
            const preco = document.querySelector('input[name="preco"]').value;
            const senha = document.querySelector('input[name="senha"]').value;
            const regioes = document.querySelectorAll('input[name="regioes[]"]');
            
            if (!nome || !cpf || !descricao || !preco || !senha) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos!');
                return false;
            }
            
            if (cpf.replace(/\D/g, '').length !== 11) {
                e.preventDefault();
                alert('CPF inválido! Deve conter 11 dígitos.');
                return false;
            }
            
            if (preco <= 0) {
                e.preventDefault();
                alert('Preço deve ser maior que zero!');
                return false;
            }
            
            if (senha.length < 4) {
                e.preventDefault();
                alert('A senha deve ter no mínimo 4 caracteres!');
                return false;
            }
            
            let temRegiao = false;
            regioes.forEach(regiao => {
                if (regiao.value.trim() !== '') {
                    temRegiao = true;
                }
            });
            
            if (!temRegiao) {
                e.preventDefault();
                alert('Adicione pelo menos uma região de atendimento!');
                return false;
            }
        });
        
        // Formatação do preço
        document.querySelector('input[name="preco"]').addEventListener('blur', function(e) {
            let value = parseFloat(e.target.value);
            if (!isNaN(value)) {
                e.target.value = value.toFixed(2);
            }
        });
    </script>
</body>
</html>