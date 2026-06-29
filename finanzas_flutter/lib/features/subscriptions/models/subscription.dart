class Subscription {
  final int    id;
  final String name;
  final double amount;
  final int    accountId;
  final int    billingDay;
  final String billingCycle; // monthly | yearly
  final String status;       // active | paused
  final String? accountName;

  const Subscription({
    required this.id,
    required this.name,
    required this.amount,
    required this.accountId,
    required this.billingDay,
    required this.billingCycle,
    required this.status,
    this.accountName,
  });

  factory Subscription.fromJson(Map<String, dynamic> j) => Subscription(
        id:           (j['id'] as num).toInt(),
        name:         j['name'] as String,
        amount:       double.tryParse(j['amount'].toString()) ?? 0,
        accountId:    (j['account_id'] as num).toInt(),
        billingDay:   int.tryParse(j['billing_day'].toString()) ?? 1,
        billingCycle: j['billing_cycle'] as String? ?? 'monthly',
        status:       j['status'] as String? ?? 'active',
        accountName:  j['account_name'] as String?,
      );

  bool get isActive => status == 'active';
  bool get isMonthly => billingCycle == 'monthly';
}
