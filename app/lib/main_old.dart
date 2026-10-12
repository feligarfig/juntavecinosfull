// lib/main.dart
// lib/main.dart
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'dart:convert';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:photo_view/photo_view.dart';
import 'package:photo_view/photo_view_gallery.dart';

void main() {
  initializeDateFormatting('es');
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'JJVV La Flecha',
      theme: ThemeData(
        fontFamily: 'Nunito',
        primarySwatch: Colors.teal,
        useMaterial3: true,
      ),
      home: const HomeScreen(),
      debugShowCheckedModeBanner: false,
    );
  }
}

// ====== PANTALLA PRINCIPAL ======
class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});
  @override State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _selectedIndex = 0;

  final List<Widget> _screens = [
    const InicioTab(),
    const AvisosTab(),
    const GaleriaTab(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _screens[_selectedIndex],
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _selectedIndex,
        onTap: (i) => setState(() => _selectedIndex = i),
        selectedItemColor: Colors.teal,
        unselectedItemColor: Colors.grey,
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.home), label: "Inicio"),
          BottomNavigationBarItem(icon: Icon(Icons.notifications), label: "Avisos"),
          BottomNavigationBarItem(icon: Icon(Icons.photo_library), label: "Galería"),
        ],
      ),
    );
  }
}

// ====== INICIO CON CALENDARIO Y PRÓXIMOS ======
class InicioTab extends StatefulWidget {
  const InicioTab({super.key});
  @override State<InicioTab> createState() => _InicioTabState();
}

class _InicioTabState extends State<InicioTab> {
  Map<String, dynamic> eventos = {};
  List<dynamic> proximos = [];

  @override
  void initState() {
    super.initState();
    cargarDatos();
  }

  Future<void> cargarDatos() async {
    final uri = Uri.parse("https://tudominio.cl/api.php");
    final cal = await http.get(uri.replace(queryParameters: {'action': 'eventos_calendario'}));
    final prox = await http.get(uri.replace(queryParameters: {'action': 'proximos_eventos'}));

    if (cal.statusCode == 200) {
      setState(() => eventos = json.decode(cal.body));
    }
    if (prox.statusCode == 200) {
      setState(() => proximos = json.decode(prox.body));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("JJVV La Flecha", style: TextStyle(fontWeight: FontWeight.w900)),
        backgroundColor: Colors.teal.shade700,
        foregroundColor: Colors.white,
        elevation: 4,
      ),
      body: RefreshIndicator(
        onRefresh: cargarDatos,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Text("Calendario de Eventos", style: Theme.of(context).textTheme.headlineMedium!.copyWith(fontWeight: FontWeight.bold, color: Colors.teal.shade800)),
            const SizedBox(height: 20),
            CalendarWidget(eventos: eventos),
            const SizedBox(height: 40),
            if (proximos.isNotEmpty) ...[
              Text("Próximos Eventos", style: Theme.of(context).textTheme.headlineMedium!.copyWith(fontWeight: FontWeight.bold, color: Colors.teal.shade800)),
              const SizedBox(height: 20),
              ...proximos.map((e) => EventCard(evento: e)).toList(),
            ],
          ],
        ),
      ),
    );
  }
}

class CalendarWidget extends StatefulWidget {
  final Map<String, dynamic> eventos;
  const CalendarWidget({required this.eventos, super.key});

  @override State<CalendarWidget> createState() => _CalendarWidgetState();
}

class _CalendarWidgetState extends State<CalendarWidget> {
  DateTime hoy = DateTime.now();
  late DateTime mesActual;

  @override
  void initState() {
    super.initState();
    mesActual = DateTime(hoy.year, hoy.month);
  }

  void cambiarMes(int delta) {
    setState(() {
      mesActual = DateTime(mesActual.year, mesActual.month + delta);
    });
  }

  @override
  Widget build(BuildContext context) {
    final primerDia = DateTime(mesActual.year, mesActual.month, 1);
    final ultimoDia = DateTime(mesActual.year, mesActual.month + 1, 0);
    final inicioSemana = primerDia.weekday % 7;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                IconButton(onPressed: () => cambiarMes(-1), icon: const Icon(Icons.chevron_left)),
                Text(DateFormat('MMMM yyyy', 'es').format(mesActual), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                IconButton(onPressed: () => cambiarMes(1), icon: const Icon(Icons.chevron_right)),
              ],
            ),
            const Divider(),
            GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 7),
              itemCount: 42,
              itemBuilder: (context, index) {
                final dia = index - inicioSemana + 1;
                if (dia < 1 || dia > ultimoDia.day) return const SizedBox();
                final fecha = DateTime(mesActual.year, mesActual.month, dia);
                final fechaStr = DateFormat('yyyy-MM-dd').format(fecha);
                final tieneEvento = widget.eventos.containsKey(fechaStr);

                return GestureDetector(
                  onTap: tieneEvento ? () => mostrarEventosDia(context, fechaStr, widget.eventos[fechaStr]) : null,
                  child: Container(
                    margin: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: tieneEvento ? Colors.teal : Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Center(child: Text("$dia", style: TextStyle(color: tieneEvento ? Colors.white : Colors.black, fontWeight: FontWeight.bold))),
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  void mostrarEventosDia(BuildContext context, String fecha, List eventos) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: Text("Eventos del ${DateFormat('d MMMM yyyy', 'es').format(DateTime.parse(fecha))}"),
        content: SizedBox(
          width: double.maxFinite,
          child: ListView.builder(
            shrinkWrap: true,
            itemCount: eventos.length,
            itemBuilder: (_, i) => ListTile(
              title: Text(eventos[i]['Nombre_evento'], style: const TextStyle(fontWeight: FontWeight.bold)),
              subtitle: Text("${eventos[i]['Hora_inicio']?.substring(0,5)} - ${eventos[i]['Hora_fin']?.substring(0,5)}"),
            ),
          ),
        ),
      ),
    );
  }
}

class EventCard extends StatelessWidget {
  final dynamic evento;
  const EventCard({required this.evento, super.key});

  @override
  Widget build(BuildContext context) {
    final fecha = DateTime.parse(evento['Fecha_evento']);
    return Card(
      child: ListTile(
        leading: CircleAvatar(backgroundColor: Colors.teal, child: Text(DateFormat('dd').format(fecha), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold))),
        title: Text(evento['Nombre_evento'], style: const TextStyle(fontWeight: FontWeight.bold)),
        subtitle: Text("${DateFormat('d MMMM yyyy', 'es').format(fecha)} • ${evento['Hora_inicio']?.substring(0,5)} - ${evento['Hora_fin']?.substring(0,5)}"),
      ),
    );
  }
}

// ====== PANTALLA DE AVISOS ======
class AvisosTab extends StatefulWidget {
  const AvisosTab({super.key});
  @override State<AvisosTab> createState() => _AvisosTabState();
}

class _AvisosTabState extends State<AvisosTab> {
  List<dynamic> avisos = [];
  bool cargando = true;

  @override
  void initState() {
    super.initState();
    cargarAvisos();
  }

  Future<void> cargarAvisos() async {
    final res = await http.get(Uri.parse("https://tudominio.cl/api.php?action=avisos"));
    if (res.statusCode == 200) {
      setState(() {
        avisos = json.decode(res.body);
        cargando = false;
      });
    }
  }

  String mesenEspanol(String fecha) {
    final meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    final partes = fecha.split('-');
    return "${meses[int.parse(partes[1])-1]} ${partes[0]}";
  }

  @override
  Widget build(BuildContext context) {
    if (cargando) return const Center(child: CircularProgressIndicator());

    return Scaffold(
      appBar: AppBar(title: const Text("Avisos e Historial"), backgroundColor: Colors.teal.shade700, foregroundColor: Colors.white),
      body: avisos.isEmpty
          ? const Center(child: Text("No hay avisos aún", style: TextStyle(fontSize: 20)))
          : ListView.builder(
              itemCount: avisos.length,
              padding: const EdgeInsets.all(16),
              itemBuilder: (context, i) {
                final a = avisos[i];
                final fecha = a['fecha'];
                final esEvento = a['tipo'] == 'evento';
                final esFeria = a['tipo_reserva'] == 'Feria artesanal';

                return Card(
                  color: esEvento ? Colors.green.shade50 : Colors.teal.shade50,
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: esEvento ? Colors.green : Colors.teal,
                      child: Text(DateFormat('dd').format(DateTime.parse(fecha)), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                    ),
                    title: Text(a['titulo'], style: const TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: a['detalle'] != null ? Text('"${a['detalle']}"', style: const TextStyle(fontStyle: FontStyle.italic)) : null,
                    trailing: Text("${a['Hora_inicio']?.substring(0,5) ?? ''} ${a['Hora_fin'] != null ? '- ${a['Hora_fin'].substring(0,5)}' : ''}"),
                    isThreeLine: true,
                  ),
                );
              },
            ),
    );
  }
}

// ====== GALERÍA COMPLETA ======
class GaleriaTab extends StatefulWidget {
  const GaleriaTab({super.key});
  @override State<GaleriaTab> createState() => _GaleriaTabState();
}

class _GaleriaTabState extends State<GaleriaTab> {
  List<dynamic> imagenes = [];

  @override
  void initState() {
    super.initState();
    http.get(Uri.parse("https://tudominio.cl/api.php?action=galeria")).then((res) {
      if (res.statusCode == 200) {
        setState(() => imagenes = json.decode(res.body));
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Galería"), backgroundColor: Colors.teal.shade700, foregroundColor: Colors.white),
      body: imagenes.isEmpty
          ? const Center(child: Text("Cargando fotos..."))
          : GridView.builder(
              padding: const EdgeInsets.all(8),
             gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
  crossAxisCount: 3,
  crossAxisSpacing: 4,
  mainAxisSpacing: 4,
),
              itemCount: imagenes.length,
              itemBuilder: (context, i) {
                final img = imagenes[i];
                return GestureDetector(
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => FullScreenGallery(imagenes: imagenes, initialIndex: i)),
                  ),
                  child: Hero(
                    tag: img['ruta'],
                    child: CachedNetworkImage(
                      imageUrl: "https://tudominio.cl/${img['ruta']}",
                      placeholder: (_, __) => Container(color: Colors.grey.shade200),
                      fit: BoxFit.cover,
                    ),
                  ),
                );
              },
            ),
    );
  }
}

class FullScreenGallery extends StatefulWidget {
  final List<dynamic> imagenes;
  final int initialIndex;
  const FullScreenGallery({required this.imagenes, required this.initialIndex, super.key});

  @override State<FullScreenGallery> createState() => _FullScreenGalleryState();
}

class _FullScreenGalleryState extends State<FullScreenGallery> {
  late PageController controller;

  @override
  void initState() {
    super.initState();
    controller = PageController(initialPage: widget.initialIndex);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(backgroundColor: Colors.black, foregroundColor: Colors.white),
      body: PhotoViewGallery.builder(
        pageController: controller,
        itemCount: widget.imagenes.length,
        builder: (context, i) {
          final img = widget.imagenes[i];
          return PhotoViewGalleryPageOptions(
            imageProvider: NetworkImage("/${img['ruta']}"),
            heroAttributes: PhotoViewHeroAttributes(tag: img['ruta']),
          );
        },
      ),
    );
  }
}