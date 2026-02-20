<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $conn = DB::connection('tenant');

        // Carregar IDs dos roles
        $roles = $conn->table('roles')->pluck('id', 'nome')->toArray();

        // Carregar IDs das permissions
        $permissions = $conn->table('permissions')->pluck('id', 'nome')->toArray();

        // Matriz de permissoes por perfil (conforme RN-305 a RN-320)
        // X = acesso (com ou sem restricao por secretaria — tratado no middleware)
        $matriz = [
            'administrador_geral' => [
                'contrato.visualizar', 'contrato.criar', 'contrato.editar', 'contrato.excluir',
                'aditivo.visualizar', 'aditivo.criar', 'aditivo.aprovar',
                'fornecedor.visualizar', 'fornecedor.criar', 'fornecedor.editar', 'fornecedor.excluir',
                'documento.visualizar', 'documento.criar', 'documento.excluir',
                'financeiro.visualizar', 'financeiro.registrar_empenho',
                'fiscal.visualizar', 'fiscal.criar', 'fiscal.editar',
                'relatorio.visualizar', 'relatorio.gerar',
                'usuario.visualizar', 'usuario.criar', 'usuario.editar', 'usuario.desativar',
                'configuracao.visualizar', 'configuracao.editar',
                'auditoria.visualizar',
                'parecer.visualizar', 'parecer.emitir',
                'workflow.visualizar', 'workflow.aprovar',
                'secretaria.visualizar', 'secretaria.criar', 'secretaria.editar', 'secretaria.excluir',
            ],
            'controladoria' => [
                'contrato.visualizar',
                'aditivo.visualizar', 'aditivo.aprovar',
                'fornecedor.visualizar',
                'documento.visualizar',
                'financeiro.visualizar',
                'fiscal.visualizar',
                'relatorio.visualizar', 'relatorio.gerar',
                'auditoria.visualizar',
                'parecer.visualizar', 'parecer.emitir',
                'workflow.visualizar', 'workflow.aprovar',
                'secretaria.visualizar',
            ],
            'secretario' => [
                'contrato.visualizar',
                'aditivo.visualizar', 'aditivo.aprovar',
                'fornecedor.visualizar',
                'documento.visualizar',
                'financeiro.visualizar',
                'fiscal.visualizar',
                'secretaria.visualizar',
                'workflow.visualizar',
            ],
            'gestor_contrato' => [
                'contrato.visualizar', 'contrato.criar', 'contrato.editar',
                'aditivo.visualizar', 'aditivo.criar',
                'fornecedor.visualizar', 'fornecedor.criar', 'fornecedor.editar',
                'documento.visualizar', 'documento.criar',
                'financeiro.visualizar',
                'fiscal.visualizar', 'fiscal.criar', 'fiscal.editar',
                'secretaria.visualizar',
                'workflow.visualizar',
            ],
            'fiscal_contrato' => [
                'contrato.visualizar',
                'aditivo.visualizar',
                'fornecedor.visualizar',
                'documento.visualizar', 'documento.criar',
                'fiscal.visualizar',
                'secretaria.visualizar',
            ],
            'financeiro' => [
                'contrato.visualizar',
                'fornecedor.visualizar',
                'documento.visualizar',
                'financeiro.visualizar', 'financeiro.registrar_empenho',
                'relatorio.visualizar', 'relatorio.gerar',
                'secretaria.visualizar',
            ],
            'procuradoria' => [
                'contrato.visualizar',
                'aditivo.visualizar', 'aditivo.aprovar',
                'fornecedor.visualizar',
                'documento.visualizar',
                'parecer.visualizar', 'parecer.emitir',
                'workflow.visualizar', 'workflow.aprovar',
                'secretaria.visualizar',
            ],
            'gabinete' => [
                'contrato.visualizar',
                'fornecedor.visualizar',
                'documento.visualizar',
                'financeiro.visualizar',
                'relatorio.visualizar',
                'secretaria.visualizar',
            ],
        ];

        foreach ($matriz as $roleName => $permissionNames) {
            $roleId = $roles[$roleName] ?? null;
            if (! $roleId) {
                continue;
            }

            foreach ($permissionNames as $permissionName) {
                $permissionId = $permissions[$permissionName] ?? null;
                if (! $permissionId) {
                    continue;
                }

                $conn->table('role_permissions')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
