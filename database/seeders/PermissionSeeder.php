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
            ['nome' => 'documento.configurar', 'descricao' => 'Configurar checklist de documentos obrigatórios', 'grupo' => 'documento'],

            // Financeiro
            ['nome' => 'financeiro.visualizar', 'descricao' => 'Visualizar dados financeiros', 'grupo' => 'financeiro'],
            ['nome' => 'financeiro.registrar_empenho', 'descricao' => 'Registrar empenhos e pagamentos', 'grupo' => 'financeiro'],

            // Fiscal
            ['nome' => 'fiscal.visualizar', 'descricao' => 'Visualizar fiscais', 'grupo' => 'fiscal'],
            ['nome' => 'fiscal.criar', 'descricao' => 'Criar/designar fiscais', 'grupo' => 'fiscal'],
            ['nome' => 'fiscal.editar', 'descricao' => 'Editar fiscais', 'grupo' => 'fiscal'],

            // Relatorio
            ['nome' => 'relatorio.visualizar', 'descricao' => 'Visualizar relatórios', 'grupo' => 'relatorio'],
            ['nome' => 'relatorio.gerar', 'descricao' => 'Gerar relatórios', 'grupo' => 'relatorio'],

            // Usuario
            ['nome' => 'usuario.visualizar', 'descricao' => 'Visualizar usuários', 'grupo' => 'usuario'],
            ['nome' => 'usuario.criar', 'descricao' => 'Criar usuários', 'grupo' => 'usuario'],
            ['nome' => 'usuario.editar', 'descricao' => 'Editar usuários', 'grupo' => 'usuario'],
            ['nome' => 'usuario.desativar', 'descricao' => 'Desativar usuários', 'grupo' => 'usuario'],

            // Configuracao
            ['nome' => 'configuracao.visualizar', 'descricao' => 'Visualizar configurações', 'grupo' => 'configuracao'],
            ['nome' => 'configuracao.editar', 'descricao' => 'Editar configurações do sistema', 'grupo' => 'configuracao'],

            // Auditoria
            ['nome' => 'auditoria.visualizar', 'descricao' => 'Visualizar logs de auditoria', 'grupo' => 'auditoria'],
            ['nome' => 'auditoria.exportar', 'descricao' => 'Exportar logs de auditoria (PDF/CSV)', 'grupo' => 'auditoria'],
            ['nome' => 'auditoria.verificar_integridade', 'descricao' => 'Verificar integridade de documentos manualmente', 'grupo' => 'auditoria'],

            // Parecer
            ['nome' => 'parecer.visualizar', 'descricao' => 'Visualizar pareceres', 'grupo' => 'parecer'],
            ['nome' => 'parecer.emitir', 'descricao' => 'Emitir pareceres', 'grupo' => 'parecer'],

            // Workflow
            ['nome' => 'workflow.visualizar', 'descricao' => 'Visualizar workflows de aprovação', 'grupo' => 'workflow'],
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
            ['nome' => 'painel-risco.exportar', 'descricao' => 'Exportar relatório de risco TCE', 'grupo' => 'painel-risco'],

            // Encerramento (IMP-052)
            ['nome' => 'encerramento.visualizar', 'descricao' => 'Visualizar processo de encerramento', 'grupo' => 'encerramento'],
            ['nome' => 'encerramento.iniciar', 'descricao' => 'Iniciar processo de encerramento', 'grupo' => 'encerramento'],
            ['nome' => 'encerramento.verificar_financeiro', 'descricao' => 'Verificar situação financeira no encerramento', 'grupo' => 'encerramento'],
            ['nome' => 'encerramento.registrar_termo', 'descricao' => 'Registrar termos de recebimento', 'grupo' => 'encerramento'],
            ['nome' => 'encerramento.avaliar', 'descricao' => 'Registrar avaliação fiscal no encerramento', 'grupo' => 'encerramento'],
            ['nome' => 'encerramento.quitar', 'descricao' => 'Registrar quitação e finalizar encerramento', 'grupo' => 'encerramento'],

            // Ocorrencia (IMP-054)
            ['nome' => 'ocorrencia.visualizar', 'descricao' => 'Visualizar ocorrências contratuais', 'grupo' => 'ocorrencia'],
            ['nome' => 'ocorrencia.criar', 'descricao' => 'Registrar ocorrências', 'grupo' => 'ocorrencia'],
            ['nome' => 'ocorrencia.resolver', 'descricao' => 'Resolver ocorrências pendentes', 'grupo' => 'ocorrencia'],

            // Relatorio Fiscal (IMP-054)
            ['nome' => 'relatorio_fiscal.visualizar', 'descricao' => 'Visualizar relatórios fiscais', 'grupo' => 'relatorio_fiscal'],
            ['nome' => 'relatorio_fiscal.criar', 'descricao' => 'Registrar relatórios fiscais', 'grupo' => 'relatorio_fiscal'],

            // LGPD (RN-213)
            ['nome' => 'lgpd.visualizar', 'descricao' => 'Visualizar solicitações LGPD', 'grupo' => 'lgpd'],
            ['nome' => 'lgpd.solicitar', 'descricao' => 'Criar solicitação LGPD', 'grupo' => 'lgpd'],
            ['nome' => 'lgpd.processar', 'descricao' => 'Processar anonimização LGPD', 'grupo' => 'lgpd'],

            // SIC/e-SIC — Solicitações LAI (IMP-058)
            ['nome' => 'lai.visualizar', 'descricao' => 'Visualizar solicitações LAI', 'grupo' => 'lai'],
            ['nome' => 'lai.analisar', 'descricao' => 'Marcar solicitação LAI em análise', 'grupo' => 'lai'],
            ['nome' => 'lai.responder', 'descricao' => 'Registrar resposta a solicitação LAI', 'grupo' => 'lai'],
            ['nome' => 'lai.prorrogar', 'descricao' => 'Prorrogar prazo de solicitação LAI', 'grupo' => 'lai'],
            ['nome' => 'lai.indeferir', 'descricao' => 'Indeferir solicitação LAI', 'grupo' => 'lai'],
            ['nome' => 'lai.relatorio', 'descricao' => 'Gerar relatório de transparência LAI', 'grupo' => 'lai'],

            // Classificação de Sigilo (IMP-056 — LAI 12.527/2011)
            ['nome' => 'classificacao.visualizar', 'descricao' => 'Visualizar classificação de sigilo', 'grupo' => 'classificacao'],
            ['nome' => 'classificacao.classificar', 'descricao' => 'Classificar/alterar sigilo de contrato', 'grupo' => 'classificacao'],
            ['nome' => 'classificacao.desclassificar', 'descricao' => 'Desclassificar contrato para público', 'grupo' => 'classificacao'],
            ['nome' => 'classificacao.justificar', 'descricao' => 'Editar justificativa de sigilo', 'grupo' => 'classificacao'],
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
