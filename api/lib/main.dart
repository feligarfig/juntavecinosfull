import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

void main() => runApp(const JJVVApp());

class JJVVApp extends StatelessWidget {
  const JJVVApp({super.key});
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'JJVV La Flecha',
      theme: ThemeData(fontFamily: 'Nunito', primarySwatch: Colors.teal),
      home: const HomeScreen(),
      debugShowCheckedModeBanner: false,
    );
  }
}

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<String> fotos = [];
  Map<String, dynamic> eventos = {};
  List proximos = [];

  @override
  void initState() {
    super.initState();
    cargarDatos();
  }

  Future<void> cargarDatos() async {
    final base = "http://juntavecinos.test/api";

    final f = await http.get(Uri.parse("$base/fotos.php"));
    final e = await http.get(Uri.parse("$base/eventos.php"));
    final p = await http.get(Uri.parse("$base/proximos.php"));

    setState(() {
      fotos = List<String>.from(json.decode(f.body));
      eventos = json.decode(e.body);
      proximos = json.decode(p.body);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            expandedHeight: 200,
            floating: false,
            pinned: true,
            flexibleSpace: FlexibleSpaceBar(
              title: const Text('JJVV LA FLECHA', style: TextStyle(fontWeight: FontWeight.w900)),
              background: Container(decoration: const BoxDecoration(gradient: LinearGradient(colors: [Colors.emerald, Colors.teal]))),
            ),
          ),

          // CARRUSEL
          if (fotos.isNotEmpty)
            SliverToBoxAdapter(
              child: SizedBox(
                height: 300,
                child: PageView.builder(
                  itemCount: fotos.length,
                  itemBuilder: (ctx, i) => Image.network(fotos[i], fit: BoxFit.cover),
                ),
              ),
            ),

          // PRÓXIMOS EVENTOS
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Text("Próximos Eventos", style: Theme.of(context).textTheme.headlineMedium!.copyWith(fontWeight: FontWeight.w900, color: Colors.teal[800])),
            ),
          ),
          SliverList(
            delegate: SliverChildBuilderDelegate(
              (ctx, i) {
                final e = proximos[i];
                final fecha = DateTime.parse(e['fecha']);
                return Card(
                  margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                  child: ListTile(
                    leading: CircleAvatar(backgroundColor: Colors.teal, child: Text("${fecha.day}", style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold))),
                    title: Text(e['nombre'], style: const TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: Text("${e['inicio'].substring(0,5)} - ${e['fin'].substring(0,5)} hrs"),
                  ),
                );
              },
              childCount: proximos.length,
            ),
          ),

          // CALENDARIO (simplificado pero bonito)
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Text("Calendario Completo en la web", style: TextStyle(color: Colors.teal[700], fontStyle: FontStyle.italic)),
            ),
          ),
        ],
      ),
    );
  }
}