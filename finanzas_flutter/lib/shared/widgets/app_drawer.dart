import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/constants.dart';
import '../../features/auth/data/auth_repository.dart';
import '../../features/auth/providers/auth_provider.dart';

class AppDrawer extends ConsumerWidget {
  const AppDrawer({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final auth = ref.watch(authProvider);

    return Drawer(
      child: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── Header ──────────────────────────────────────────────
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 24, 20, 8),
              child: Row(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: AppColors.primary,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.account_balance_wallet,
                        color: Colors.white, size: 24),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          auth?.name ?? 'Usuario',
                          style: const TextStyle(
                              fontWeight: FontWeight.w700,
                              fontSize: 15,
                              color: AppColors.textPrimary),
                          overflow: TextOverflow.ellipsis,
                        ),
                        Text(
                          auth?.email ?? '',
                          style: const TextStyle(
                              fontSize: 12,
                              color: AppColors.textSecondary),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const Divider(height: 24),

            // ── Navegación extra ─────────────────────────────────────
            _DrawerItem(
              icon: Icons.handshake_outlined,
              label: 'Préstamos',
              onTap: () { Navigator.of(context).pop(); context.go('/loans'); },
            ),
            _DrawerItem(
              icon: Icons.subscriptions_outlined,
              label: 'Suscripciones',
              onTap: () { Navigator.of(context).pop(); context.go('/subscriptions'); },
            ),
            _DrawerItem(
              icon: Icons.label_outline,
              label: 'Categorías',
              onTap: () { Navigator.of(context).pop(); context.go('/categories'); },
            ),

            const Spacer(),
            const Divider(),

            // ── Logout ───────────────────────────────────────────────
            ListTile(
              leading: const Icon(Icons.logout, color: Color(0xFFEF5350)),
              title: const Text('Cerrar sesión',
                  style: TextStyle(color: Color(0xFFEF5350))),
              onTap: () async {
                Navigator.of(context).pop();
                await AuthRepository().logout();
                ref.read(authProvider.notifier).state = null;
                if (context.mounted) context.go('/login');
              },
            ),
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }
}

class _DrawerItem extends StatelessWidget {
  final IconData icon;
  final String   label;
  final VoidCallback onTap;

  const _DrawerItem({
    required this.icon,
    required this.label,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Icon(icon),
      title: Text(label),
      onTap: onTap,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 2),
    );
  }
}
