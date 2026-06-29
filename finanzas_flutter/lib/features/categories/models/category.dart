class Category {
  final int    id;
  final String name;
  final String type; // income | expense

  const Category({
    required this.id,
    required this.name,
    required this.type,
  });

  factory Category.fromJson(Map<String, dynamic> j) => Category(
        id:   (j['id'] as num).toInt(),
        name: j['name'] as String,
        type: j['type'] as String,
      );

  bool get isIncome => type == 'income';
}
