<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Contrato
            ['nome' => 'contrato.visualizar', 'descricao' => 'Visualizar contratos', 'grupo' => 'contrato'],
            ['nome' => 'contrato.criar', 'descricao' => 'Criar contratos', 'grupo' => 'contrato'],
            ['nome' => 'contrato.editar', 'descricao' => 'Editar contratos', 'grupo' => 'contrato'],
            ['nome' => 'contrato.excluir', 'descricao' => 'Excluir contratos', 'grupo' => 'contrato'],

            // Aditivo
            ['nome' => 'aditivo.visualizar', 'descricao' => 'Visualizar aditivos', 'grupo' => 'aditivo'],
            ['nome' => 'aditivo.criar', 'descricao' => 'Criar aditivos', 'grupo' => 'aditivo'],
            ['nome' => 'aditivo.aprovar', 'descricao' => 'Aprovar aditivos no workflow', 'grupo' => 'aditivo'],

            // Fornecedor
            ['nome' => 'fornecedor.visualizar', 'descricao' => 'Visualizar fornecedores', 'grupo' => 'fornecedor'],
            ['nome' => 'fornecedor.criar', 'descricao' => 'Criar fornecedores', 'grupo' => 'fornecedor'],
            ['nome' => 'fornecedor.editar', 'descricao' => 'Editar fornecedores', 'grupo' => 'fornecedor'],
            ['nome' => 'fornecedor.excluir', 'descricao' => 'Excluir fornecedores', 'grupo' => 'fornecedor'],

            // Documento
            ['nome' => 'documento.visualizar', 'descricao' => 'Visualizar documentos', 'grupo' => 'documento'],
            ['nome' => 'documento.criar', 'descricao' => 'Criar/upload de documentos', 'grupo' => 'documento'],
            ['nome' => 'documento.download', 'descricao' => 'Download de documentos', 'grupo' => 'documento'],
            ['nome' => 'documento.excluir', 'descricao' => 'Excluir documentos', 'grupo' => 'documento'],
            ['nome' => 'documento.configurar', 'descricao' => 'Configurar checklist de documentos obrigatorios', 'grupo' => 'documento'],

            // Financeiro
            ['nome' => 'financeiro.visualizar', 'descricao' => 'Visualizar dados financeiros', 'grupo' => 'financeiro'],
            ['nome' => 'financeiro.registrar_empenho', 'descricao' => 'Registrar empenhos e pagamentos', 'grupo' => 'financeiro'],

            // Fiscal
            ['nome' => 'fiscal.visualizar', 'descricao' => 'Visualizar fiscais', 'grupo' => 'fiscal'],
            ['nome' => 'fiscal.criar', 'descricao' => 'Criar/designar fiscais', 'grupo' => 'fiscal'],
            ['nome' => 'fiscal.editar', 'descricao' => 'Editar fiscais', 'grupo' => 'fiscal'],

            // Relatorio
            ['nome' => 'relatorio.visualizar', 'descricao' => 'Visualizar relatorios', 'grupo' => 'relatorio'],
            ['nome' => 'relatorio.gerar', 'descricao' => 'Gerar relatorios', 'grupo' => 'relatorio'],

            // Usuario
            ['nome' => 'usuario.visualizar', 'descricao' => 'Visualizar usuarios', 'grupo' => 'usuario'],
            ['nome' => 'usuario.criar', 'descricao' => 'Criar usuarios', 'grupo' => 'usuario'],
            ['nome' => 'usuario.editar', 'descricao' => 'Editar usuarios', 'grupo' => 'usuario'],
            ['nome' => 'usuario.desativar', 'descricao' => 'Desativar usuarios', 'grupo' => 'usuario'],

            // Configuracao
            ['nome' => 'configuracao.visualizar', 'descricao' => 'Visualizar configuracoes', 'grupo' => 'configuracao'],
            ['nome' => 'configuracao.editar', 'descricao' => 'Editar configuracoes do sistema', 'grupo' => 'configuracao'],

            // Auditoria
            ['nome' => 'auditoria.visualizar', 'descricao' => 'Visualizar logs de auditoria', 'grupo' => 'auditoria'],
            ['nome' => 'auditoria.exportar', 'descricao' => 'Exportar logs de auditoria (PDF/CSV)', 'grupo' => 'auditoria'],
            ['nome' => 'auditoria.verificar_integridade', 'descricao' => 'Verificar integridade de documentos manualmente', 'grupo' => 'auditoria'],

            // Parecer
            ['nome' => 'parecer.visualizar', 'descricao' => 'Visualizar pareceres', 'grupo' => 'parecer'],
            ['nome' => 'parecer.emitir', 'descricao' => 'Emitir pareceres', 'grupo' => 'parecer'],

            // Workflow
            ['nome' => 'workflow.visualizar', 'descricao' => 'Visualizar workflows de aprovacao', 'grupo' => 'workflow'],
            ['nome' => 'workflow.aprovar', 'descricao' => 'Aprovar etapas de workflow', 'grupo' => 'workflow'],

            // Servidor
            ['nome' => 'servidor.visualizar', 'descricao' => 'Visualizar servidores', 'grupo' => 'servidor'],
            ['nome' => 'servidor.criar', 'descricao' => 'Criar servidores', 'grupo' => 'servidor'],
            ['nome' => 'servidor.editar', 'descricao' => 'Editar servidores', 'grupo' => 'servidor'],
            ['nome' => 'servidor.excluir', 'descricao' => 'Excluir servidores', 'grupo' => 'servidor'],

            // Secretaria
            ['nome' => 'secretaria.visualizar', 'descricao' => 'Visualizar secretarias', 'grupo' => 'secretaria'],
            ['nome' => 'secretaria.criar', 'descricao' => 'Criar secretarias', 'grupo' => 'secretaria'],
            ['nome' => 'secretaria.editar', 'descricao' => 'Editar secretarias', 'grupo' => 'secretaria'],
            ['nome' => 'secretaria.excluir', 'descricao' => 'Excluir secretarias', 'grupo' => 'secretaria'],

            // Alerta
            ['nome' => 'alerta.visualizar', 'descricao' => 'Visualizar alertas de vencimento', 'grupo' => 'alerta'],
            ['nome' => 'alerta.resolver', 'descricao' => 'Resolver/fechar alertas manualmente', 'grupo' => 'alerta'],

            // Dashboard
            ['nome' => 'dashboard.visualizar', 'descricao' => 'Visualizar dashboard executivo', 'grupo' => 'dashboard'],
            ['nome' => 'dashboard.atualizar', 'descricao' => 'Atualizar dados do dashboard manualmente', 'grupo' => 'dashboard'],

            // Painel de Risco
            ['nome' => 'painel-risco.visualizar', 'descricao' => 'Visualizar painel de risco', 'grupo' => 'painel-risco'],
            ['nome' => 'painel-risco.exportar', 'descricao' => 'Exportar relatorio de risco TCE', 'grupo' => 'painel-risco'],

            // LGPD (RN-213)
            ['nome' => 'lgpd.visualizar', 'descricao' => 'Visualizar solicitacoes LGPD', 'grupo' => 'lgpd'],
            ['nome' => 'lgpd.solicitar', 'descricao' => 'Criar solicitacao LGPD', 'grupo' => 'lgpd'],
            ['nome' => 'lgpd.processar', 'descricao' => 'Processar anonimizacao LGPD', 'grupo' => 'lgpd'],
        ];

        foreach ($permissions as $permission) {
            DB::connection('tenant')->table('permissions')->updateOrInsert(
                ['nome' => $permission['nome']],
                array_merge($permission, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
