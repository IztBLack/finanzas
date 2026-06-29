import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import 'features/auth/screens/splash_screen.dart';
import 'features/auth/screens/login_screen.dart';
import 'features/dashboard/screens/dashboard_screen.dart';
import 'features/accounts/screens/accounts_list_screen.dart';
import 'features/accounts/screens/account_form_screen.dart';
import 'features/transactions/screens/transactions_list_screen.dart';
import 'features/transactions/screens/transaction_form_screen.dart';
import 'features/categories/screens/categories_list_screen.dart';
import 'features/categories/screens/category_form_screen.dart';
import 'features/loans/screens/loans_list_screen.dart';
import 'features/loans/screens/loan_form_screen.dart';
import 'features/loans/screens/loan_detail_screen.dart';
import 'features/subscriptions/screens/subscriptions_list_screen.dart';
import 'features/subscriptions/screens/subscription_form_screen.dart';
import 'shared/theme/app_theme.dart';
import 'shared/widgets/app_drawer.dart';

// ── Router ────────────────────────────────────────────────────────────────
final routerProvider = Provider<GoRouter>((ref) {
  return GoRouter(
    initialLocation: '/splash',
    routes: [
      GoRoute(path: '/splash', builder: (ctx, _) => const SplashScreen()),
      GoRoute(path: '/login',  builder: (ctx, _) => const LoginScreen()),

      // ── Shell con bottom nav ──────────────────────────────────────
      StatefulShellRoute.indexedStack(
        builder: (ctx, state, shell) => _HomeShell(shell: shell),
        branches: [
          StatefulShellBranch(routes: [
            GoRoute(path: '/dashboard',
                builder: (ctx, _) => const DashboardScreen()),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(path: '/transactions',
                builder: (ctx, _) => const TransactionsListScreen()),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(path: '/accounts',
                builder: (ctx, _) => const AccountsListScreen()),
          ]),
        ],
      ),

      // ── Formularios de cuentas ────────────────────────────────────
      GoRoute(path: '/accounts/add',
          builder: (ctx, _) => const AccountFormScreen()),
      GoRoute(path: '/accounts/:id/edit',
          builder: (ctx, s) => AccountFormScreen(accountId: int.tryParse(s.pathParameters['id'] ?? ''))),

      // ── Formularios de transacciones ─────────────────────────────
      GoRoute(path: '/transactions/add',
          builder: (ctx, _) => const TransactionFormScreen()),
      GoRoute(path: '/transactions/:id/edit',
          builder: (ctx, s) => TransactionFormScreen(
              transactionId: int.tryParse(s.pathParameters['id'] ?? ''))),

      // ── Categorías ───────────────────────────────────────────────
      GoRoute(path: '/categories',
          builder: (ctx, _) => const CategoriesListScreen()),
      GoRoute(path: '/categories/add',
          builder: (ctx, _) => const CategoryFormScreen()),
      GoRoute(path: '/categories/:id/edit',
          builder: (ctx, s) => CategoryFormScreen(
              categoryId: int.tryParse(s.pathParameters['id'] ?? ''))),

      // ── Préstamos ────────────────────────────────────────────────
      GoRoute(path: '/loans',
          builder: (ctx, _) => const LoansListScreen()),
      GoRoute(path: '/loans/add',
          builder: (ctx, _) => const LoanFormScreen()),
      GoRoute(path: '/loans/:id',
          builder: (ctx, s) =>
              LoanDetailScreen(loanId: int.parse(s.pathParameters['id']!))),
      GoRoute(path: '/loans/:id/edit',
          builder: (ctx, s) => LoanFormScreen(
              loanId: int.tryParse(s.pathParameters['id'] ?? ''))),

      // ── Suscripciones ────────────────────────────────────────────
      GoRoute(path: '/subscriptions',
          builder: (ctx, _) => const SubscriptionsListScreen()),
      GoRoute(path: '/subscriptions/add',
          builder: (ctx, _) => const SubscriptionFormScreen()),
      GoRoute(path: '/subscriptions/:id/edit',
          builder: (ctx, s) => SubscriptionFormScreen(
              subscriptionId: int.tryParse(s.pathParameters['id'] ?? ''))),
    ],
  );
});

// ── HomeShell con bottom nav + drawer ────────────────────────────────────
class _HomeShell extends StatelessWidget {
  final StatefulNavigationShell shell;
  const _HomeShell({required this.shell});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: shell,
      drawer: const AppDrawer(),
      bottomNavigationBar: NavigationBar(
        selectedIndex: shell.currentIndex,
        onDestinationSelected: (i) =>
            shell.goBranch(i, initialLocation: i == shell.currentIndex),
        destinations: const [
          NavigationDestination(
              icon: Icon(Icons.home_outlined),
              selectedIcon: Icon(Icons.home),
              label: 'Inicio'),
          NavigationDestination(
              icon: Icon(Icons.swap_horiz_outlined),
              selectedIcon: Icon(Icons.swap_horiz),
              label: 'Movimientos'),
          NavigationDestination(
              icon: Icon(Icons.account_balance_wallet_outlined),
              selectedIcon: Icon(Icons.account_balance_wallet),
              label: 'Cuentas'),
        ],
      ),
    );
  }
}

// ── App ───────────────────────────────────────────────────────────────────
class MyApp extends ConsumerWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(routerProvider);
    return MaterialApp.router(
      title: 'Finanzas',
      theme: AppTheme.dark,
      routerConfig: router,
      debugShowCheckedModeBanner: false,
    );
  }
}
