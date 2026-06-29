import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/categories_provider.dart';
import '../models/category.dart';

class CategoryFormScreen extends ConsumerStatefulWidget {
  final int? categoryId;
  const CategoryFormScreen({super.key, this.categoryId});

  @override
  ConsumerState<CategoryFormScreen> createState() => _CategoryFormScreenState();
}

class _CategoryFormScreenState extends ConsumerState<CategoryFormScreen> {
  final _formKey  = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  String _type    = 'expense';
  bool   _loading = false;

  @override
  void initState() {
    super.initState();
    if (widget.categoryId != null) {
      final cats = ref.read(categoriesProvider).valueOrNull ?? [];
      final cat = cats.cast<Category?>()
          .firstWhere((c) => c?.id == widget.categoryId, orElse: () => null);
      if (cat != null) {
        _nameCtrl.text = cat.name;
        _type          = cat.type;
      }
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);
    try {
      final data = {'name': _nameCtrl.text.trim(), 'type': _type};
      if (widget.categoryId != null) {
        await ref.read(categoriesProvider.notifier).edit(widget.categoryId!, data);
      } else {
        await ref.read(categoriesProvider.notifier).create(data);
      }
      if (mounted) Navigator.of(context).pop(true);
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  void dispose() { _nameCtrl.dispose(); super.dispose(); }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.categoryId != null ? 'Editar categoría' : 'Nueva categoría'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              TextFormField(
                controller: _nameCtrl,
                decoration: const InputDecoration(labelText: 'Nombre'),
                validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null,
              ),
              const SizedBox(height: 16),
              const Text('Tipo', style: TextStyle(fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),
              Row(children: [
                Expanded(child: _Chip(
                    label: 'Gasto', selected: _type == 'expense',
                    color: const Color(0xFFEF5350),
                    onTap: () => setState(() => _type = 'expense'))),
                const SizedBox(width: 10),
                Expanded(child: _Chip(
                    label: 'Ingreso', selected: _type == 'income',
                    color: const Color(0xFF00BFA5),
                    onTap: () => setState(() => _type = 'income'))),
              ]),
              const SizedBox(height: 32),
              ElevatedButton(
                onPressed: _loading ? null : _submit,
                child: _loading
                    ? const SizedBox(width: 22, height: 22,
                        child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white))
                    : Text(widget.categoryId != null ? 'Actualizar' : 'Crear categoría'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _Chip extends StatelessWidget {
  final String label;
  final bool selected;
  final Color color;
  final VoidCallback onTap;
  const _Chip({required this.label, required this.selected, required this.color, required this.onTap});

  @override
  Widget build(BuildContext context) => GestureDetector(
    onTap: onTap,
    child: AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      padding: const EdgeInsets.symmetric(vertical: 12),
      decoration: BoxDecoration(
        color: selected ? color.withOpacity(0.18) : const Color(0xFF1C2132),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: selected ? color : const Color(0xFF262D42), width: selected ? 2 : 1),
      ),
      child: Text(label, textAlign: TextAlign.center,
          style: TextStyle(fontWeight: FontWeight.w600,
              color: selected ? color : const Color(0xFF8A93A8))),
    ),
  );
}
