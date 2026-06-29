class DashboardData {
  final double totalBalance;
  final double incomeMonth;
  final double expenseMonth;
  final double monthlySubscriptions;
  final List<RecentMovement> recentTransactions;

  const DashboardData({
    required this.totalBalance,
    required this.incomeMonth,
    required this.expenseMonth,
    required this.monthlySubscriptions,
    required this.recentTransactions,
  });

  factory DashboardData.fromJson(Map<String, dynamic> j) => DashboardData(
        totalBalance:          double.tryParse(j['total_balance'].toString()) ?? 0,
        incomeMonth:           double.tryParse(j['income_month'].toString()) ?? 0,
        expenseMonth:          double.tryParse(j['expense_month'].toString()) ?? 0,
        monthlySubscriptions:  double.tryParse(j['monthly_subscriptions'].toString()) ?? 0,
        recentTransactions:    (j['recent_transactions'] as List? ?? [])
            .map((e) => RecentMovement.fromJson(e as Map<String, dynamic>))
            .toList(),
      );
}

class RecentMovement {
  final String date;
  final String description;
  final String category;
  final String account;
  final String type;
  final double amount;

  const RecentMovement({
    required this.date,
    required this.description,
    required this.category,
    required this.account,
    required this.type,
    required this.amount,
  });

  factory RecentMovement.fromJson(Map<String, dynamic> j) => RecentMovement(
        date:        j['date'] as String,
        description: j['description'] as String,
        category:    j['category'] as String,
        account:     j['account'] as String,
        type:        j['type'] as String,
        amount:      double.tryParse(j['amount'].toString()) ?? 0,
      );

  bool get isIncome => type == 'income';
}
