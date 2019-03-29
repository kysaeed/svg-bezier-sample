<?php

class SvgSample
{
	const BezierCrossThreshold = 0.001;

	public function runDemo()
	{
		self::demoBezierToLineCross();

		self::demoBezierToBezierCross();

		self::demoBezierBoundingBox();

		self::demoBezierSegmentBoundingBox();

		self::demoBezierCtoQ();
	}

	public static function demoBezierCtoQ()
	{
		echo '<hr />';
		echo '<h2>SVGのCコマンド(3次ベジェ曲線)の曲線をQコマンド(2次ベジェ曲線)の連続に変換</h2>';

		// 起点
		$s = [
			'x' => 0,
			'y' => 150,
		];

		// 終点
		$e = [
			'x' => 300,
			'y' => 150,
		];

		// 曲線を曲げる目標 その1
		$a = [
			'x' => 20,
			'y' => 0,
		];

		// 曲線を曲げる目標 その2
		$b = [
			'x' => 220,
			'y' => 0,
		];


		echo '元の曲線<br />';
		$svg = '<svg width="300" height="300">';
		$svg .= " <path d='M{$s['x']},{$s['y']} C{$a['x']},{$a['y']} {$b['x']},{$b['y']} {$e['x']},{$e['y']}' fill='none' stroke='black'/>";
		$svg .= '</svg>';
		echo $svg.'<br />';


		$qParams = self::svgCtoQ($s, [$a, $b, $e]);


		$cl = ['red', 'blue'];

		echo '分解する<br />';
		foreach ($qParams as $i => $t) {
			$svg = '<svg width="300" height="300">';
			$svg .= " <path d='M{$t[0]['x']},{$t[0]['y']} Q{$t[1]['x']},{$t[1]['y']} {$t[2]['x']},{$t[2]['y']}' fill='none' stroke='black'/>";
			$svg .= '</svg>';
			echo $svg;
		}
		echo '<br />';

		echo '分解した曲線を連続で表示<br />';
		$svg = '<svg width="300" height="300">';
		foreach ($qParams as $i => $t) {
			$svg .= " <path d='M{$t[0]['x']},{$t[0]['y']} Q{$t[1]['x']},{$t[1]['y']} {$t[2]['x']},{$t[2]['y']}' fill='none' stroke='{$cl[$i]}'/>";
		}
		$svg .= '</svg>';
		echo $svg.'<br />';
	}

	public static function demoBezierToLineCross()
	{
		echo '<hr /><h2>ベジェ曲線と直線の交点</h2>';
		echo '<p>直線と曲線の交点に黒丸でマークを付けるよ</p>';

		// 曲線
		$curve = [
			// 起点
			[
				'x' => 1,
				'y' => 180,
			],
			// カーブの目標
			[
				'x' => 170,
				'y' => 0,
			],
			// 終点
			[
				'x' => 200,
				'y' => 180,
			],
		];


		// 直線
		$line = [
			// 起点
			[
				'x' => 160,
				'y' => 130,
			],
			// 終点
			[
				'x' => 0,
				'y' => 60,
			]
		];


		$crossPointInfoList = self::getBezier2CrossPointByLine($curve, $line);

		echo '<ul>';
		if (!empty($crossPointInfoList)) {
			foreach ($crossPointInfoList as $info) {
				$p = $info['point'];
				echo "<li>交点: ({$p['x']}, {$p['y']})</li>";
			}
		} else {
			echo '<li>交点: なし</li>';
		}
		echo '</ul>';


		$s = '<svg width="500px" height="300px">';
		$s .= "<path d='M {$curve[0]['x']},{$curve[0]['y']} Q {$curve[1]['x']},{$curve[1]['y']} {$curve[2]['x']},{$curve[2]['y']}' stroke='red' fill='none' />";
		$s .= "<path d='M {$line[0]['x']},{$line[0]['y']} {$line[1]['x']},{$line[1]['y']}' stroke='blue' fill='none' />";
		$s .= "<path d='M {$curve[0]['x']},{$curve[0]['y']} {$curve[1]['x']},{$curve[1]['y']} {$curve[2]['x']},{$curve[2]['y']}' stroke='#e0e0e0' fill='none' />";

		foreach ($crossPointInfoList as $info) {
			$p = $info['point'];
			$s .= "<circle cx='{$p['x']}' cy='{$p['y']}' r='3' fill='black' />";
		}
		$s .= '</svg>';
		echo $s;

	}

	public static function demoBezierToBezierCross()
	{
		echo '<hr /><h2>ベジェ曲線とベジェ曲線の交点</h2>';
		echo '<p>曲線と曲線の交点に黒丸でマークを付けるよ</p>';

		// 曲線
		$curves = [
			[
				// 起点
				[
					'x' => 100,
					'y' => 100,
				],
				// カーブの目標
				[
					'x' => 800,
					'y' => 140,
				],
				// 終点
				[
					'x' => 100,
					'y' => 200,
				],
			],
			[
				// 起点
				[
					'x' => 190,
					'y' => 0,
				],
				// カーブの目標
				[
					'x' => 200,
					'y' => 570,
				],
				// 終点
				[
					'x' => 390,
					'y' => 140,
				],
			],
		];

		$crossPointInfoList = self::getBezier2CrossPointByBezier2($curves[0], $curves[1]);

		echo '<ul>';
		if (!empty($crossPointInfoList)) {
			foreach ($crossPointInfoList as $p) {
				echo "<li>交点: ({$p['x']}, {$p['y']})</li>";
			}
		} else {
			echo '<li>交点: なし</li>';
		}
		echo '</ul>';


		$colorList = [
			'blue',
			'red',
		];
		$svg = '<svg width="800" height="600">';
		foreach ($curves as $i => $c) {
			$col = $colorList[$i];
			$svg .= "<path d='M{$c[0]['x']},{$c[0]['y']} Q{$c[1]['x']},{$c[1]['y']} {$c[2]['x']},{$c[2]['y']}' fill='none' stroke='{$col}'/>";
		}
		foreach ($crossPointInfoList as $p) {
			$svg .= "<circle cx='{$p['x']}' cy='{$p['y']}' r='3' fill='black' />";
		}
		$svg .= '</svg>';

		echo $svg.'<br />';
	}

	public static function demoBezierBoundingBox()
	{
		echo '<hr /><h2>曲線のバウンディングボックス</h2>';
		echo '<p>青でバウンディングボックスを表示</p>';

		// 曲線
		$curve = [
			// 起点
			[
				'x' => 10,
				'y' => 200,
			],
			// カーブの目標
			[
				'x' => 600,
				'y' => 0,
			],
			// 終点
			[
				'x' => 300,
				'y' => 300,
			],
		];


		$box = self::getBezier2BoundingBox($curve, $start, $end);

		$s = '<svg width="500px" height="300px">';
		$s .= "<path d='M {$curve[0]['x']},{$curve[0]['y']} Q {$curve[1]['x']},{$curve[1]['y']} {$curve[2]['x']},{$curve[2]['y']}' stroke='black' fill='none' />";
		$s .= "<path d='M {$box[0]['x']},{$box[0]['y']} {$box[1]['x']},{$box[0]['y']} {$box[1]['x']},{$box[1]['y']} {$box[0]['x']},{$box[1]['y']} z' stroke='blue' fill='none'/>";
		$s .= '</svg>';
		echo $s;
	}

	public static function demoBezierSegmentBoundingBox()
	{
		echo '<hr /><h2>曲線の一部のバウンディングボックス</h2>';
		echo '<p>赤点で範囲、青でバウンディングボックスを表示</p>';

		// 曲線
		$curve = [
			// 起点
			[
				'x' => 10,
				'y' => 200,
			],
			// カーブの目標
			[
				'x' => 600,
				'y' => 0,
			],
			// 終点
			[
				'x' => 300,
				'y' => 300,
			],
		];


		$lineSgmentList = [
			[
				'start' => 0.2,
				'end' => 0.8,
			], [
				'start' => 0.1,
				'end' => 0.4,
			], [
				'start' => 0.5,
				'end' => 0.7,
			], [
				'start' => 0.8,
				'end' => 1.0,
			]
		];


		echo '<ul>';
		foreach ($lineSgmentList as $segment) {
			echo '<li>';

			echo "範囲{$segment['start']}〜{$segment['end']}<br />";

			$box = self::getBezier2SegmentBoundingBox($curve, $segment['start'], $segment['end']);

			$pointStart = self::getBezier2CurvePoint($curve[0], $curve[2], $curve[1], $segment['start']);
			$pointEnd = self::getBezier2CurvePoint($curve[0], $curve[2], $curve[1], $segment['end']);

			$s = '<svg width="500px" height="360px">';
			$s .= "<path d='M {$curve[0]['x']},{$curve[0]['y']} Q {$curve[1]['x']},{$curve[1]['y']} {$curve[2]['x']},{$curve[2]['y']}' stroke='black' fill='none' />";
			$s .= "<path d='M {$box[0]['x']},{$box[0]['y']} {$box[1]['x']},{$box[0]['y']} {$box[1]['x']},{$box[1]['y']} {$box[0]['x']},{$box[1]['y']} z' stroke='blue' fill='none'/>";
			$s .= "<circle cx='{$pointStart['x']}' cy='{$pointStart['y']}' r='3' fill='red' />";
			$s .= "<circle cx='{$pointEnd['x']}' cy='{$pointEnd['y']}' r='3' fill='red' />";
			$s .= '</svg>';
			echo $s;

			echo '</li>';
		}
		echo '</ul>';
	}

	public static function demoBezierSegmentTangetialLines()
	{
		echo '<hr /><h1>曲線の一部の接線</h1>';
		echo '<p>赤点で範囲、青で接線を表示</p>';

		// 曲線
		$curve = [
			// 起点
			[
				'x' => 0,
				'y' => 200,
			],
			// カーブの目標
			[
				'x' => 300,
				'y' => 200,
			],
			// 終点
			[
				'x' => 100,
				'y' => 300,
			],
		];


		$lineSgmentList = [
			[
				'start' => 0.0,
				'end' => 1.0,
			], [
				'start' => 0.2,
				'end' => 0.8,
			], [
				'start' => 0.1,
				'end' => 0.4,
			], [
				'start' => 0.5,
				'end' => 0.7,
			], [
				'start' => 0.8,
				'end' => 1.0,
			]
		];


		echo '<ul>';
		foreach ($lineSgmentList as $segment) {
			echo '<li>';

			echo "範囲:{$segment['start']}〜{$segment['end']}<br />";

			$lineList = self::getBezier2SegmentTangentialLines($curve, $segment['start'], $segment['end']);


			$pointStart = self::getBezier2CurvePoint($curve[0], $curve[2], $curve[1], $segment['start']);
			$pointEnd = self::getBezier2CurvePoint($curve[0], $curve[2], $curve[1], $segment['end']);

			$s = '<svg width="500px" height="360px">';
			$s .= "<path d='M {$curve[0]['x']},{$curve[0]['y']} Q {$curve[1]['x']},{$curve[1]['y']} {$curve[2]['x']},{$curve[2]['y']}' stroke='black' fill='none' />";
//			$s .= "<path d='M {$box[0]['x']},{$box[0]['y']} {$box[1]['x']},{$box[0]['y']} {$box[1]['x']},{$box[1]['y']} {$box[0]['x']},{$box[1]['y']} z' stroke='blue' fill='none'/>";
			if (!empty($lineList)) {
				$s .= "<path d='M ";
				foreach ($lineList as $p) {
					$s .= "{$p['x']},{$p['y']} ";
				}
				$s .= "' stroke='blue' fill='none' />";
			}

			$s .= "<circle cx='{$pointStart['x']}' cy='{$pointStart['y']}' r='3' fill='red' />";
			$s .= "<circle cx='{$pointEnd['x']}' cy='{$pointEnd['y']}' r='3' fill='red' />";

			$s .= '</svg>';
			echo $s;

			echo '</li>';
		}
		echo '</ul>';
	}

	public static function getBezier2CrossPointByLine($bezir, $line)
	{
		$lp = self::getLineParams($line[0], $line[1]);
		$a = $lp['a'];
		$b = $lp['b'];
		$c = $lp['c'];


		$b0 = $bezir[0];
		$b1 = $bezir[2];
		$cp = $bezir[1];

		$m = ($b * $b1['y']) + ($b * $b0['y']) + ($a * $b0['x']) + ($a * $b1['x']) - (2 * $b * $cp['y']) - (2 * $a * $cp['x']);
		$n = -(2 * $b * $b0['y']) - (2 * $a * $b0['x']) + (2 * $b * $cp['y']) + (2 * $a * $cp['x']);
		$l = ($b * $b0['y']) + ($a * $b0['x']) + $c;

		$tList = [];
		$d = ($n * $n) - (4 * $m * $l);
		if ($d > 0) {
			$d = sqrt($d);
			$t0 = 0.5 * (-$n + $d) / $m;
		    $t1 = 0.5 * (-$n - $d) / $m;

			if (($t0 >= 0) && ($t0 <= 1.0)) {
				$tList[] = $t0;
			}
			if(($t1 >= 0) && ($t1 <= 1.0)){
				$tList[] = $t1;
		    }
		} else if ($d == 0) {
			$t1 = 0.5 * -$n / $m;
			if(($t1 >= 0) && ($t1 <= 1.0)){
				$tList[] = $t1;
		    }
		}

		$crossPointListOnCurve = [];
		foreach ($tList as $t) {
			$crossPointListOnCurve[] = [
				'point' => self::getBezier2CurvePoint($b0, $b1, $cp, $t),
				'length' => $t,
			];
		}

		$crossPointListOnLine = [];
		foreach ($crossPointListOnCurve as $p) {
			if (self::isOnLine($line, $p['point'])) {
				$crossPointListOnLine[] = $p;
			}
		}

		return $crossPointListOnLine;
	}

	protected static function getLineLength($line, $point)
	{
		$v = [
			'x' => ($line[1]['x'] - $line[0]['x']),
			'y' => ($line[1]['y'] - $line[0]['y']),
		];

		$p = [
			'x' => ($point['x'] - $line[0]['x']),
			'y' => ($point['y'] - $line[0]['y']),
		];

		if ($v['x'] != 0) {
			return ($p['x'] / $v['x']);
		}
		if ($v['y'] != 0) {
			return ($p['y'] / $v['y']);
		}

		return 0;
	}

	protected static function isOnLine($line, $point)
	{
		$length = self::getLineLength($line, $point);
		if (($length < 0) || ($length > 1.0)) {
			return false;
		}
		return true;
	}

	protected static function svgCtoQ($s, $cParams)
	{
		$a = $cParams[0];
		$b = $cParams[1];
		$e = $cParams[2];

		// $t = 0.5;

		$k = self::getBezier3CurvePoint($s, $e, $a, $b, 0.51);

		$aByK = self::getBezier3CurvePoint($s, $e, $a, $b, 0.25);
		$bByK = self::getBezier3CurvePoint($s, $e, $a, $b, 0.75);

		$t = 0.51;
		$param = ($s['x'] * pow(1 - $t, 2)) + ($k['x'] * pow($t, 2));
		$ax = (($aByK['x'] - $param) / ((1 - $t) * $t * 2));
		$param = ($s['y'] * pow(1 - $t, 2)) + ($k['y'] * pow($t, 2));
		$ay = ($aByK['y'] - $param) / ((1 - $t) * $t * 2);

		$t = 0.49;
		$param = ($k['x'] * pow(1 - $t, 2)) + ($e['x'] * pow($t, 2));
		$bx = ($bByK['x'] - $param) / ((1 - $t) * $t * 2);
		$param = ($k['y'] * pow(1 - $t, 2)) + ($e['y'] * pow($t, 2));
		$by = ($bByK['y'] - $param) / ((1 - $t) * $t * 2);
		// dump("new a = {$ax},{$ay}");



		return [
			[
				[
					'x'=> $s['x'],
					'y'=> $s['y'],
					'isOnCurvePoint' => true,
				],
				[
					'x'=> $ax,
					'y'=> $ay,
					'isOnCurvePoint' => false,
				],
				[
					'x'=> $k['x'],
					'y'=> $k['y'],
					'isOnCurvePoint' => true,
				],
			], [
				[
					'x'=> $k['x'],
					'y'=> $k['y'],
					'isOnCurvePoint' => true,
				],
				[
					'x'=> $bx,
					'y'=> $by,
					'isOnCurvePoint' => false,
				],
				[
					'x'=> $e['x'],
					'y'=> $e['y'],
					'isOnCurvePoint' => true,
				],
			],
		];
	}

	protected static function getLineParams($v1, $v2)
	{
		$a = ($v2['y'] - $v1['y']);
		$b = ($v2['x'] - $v1['x']);
		return [
			'a' => $a,
			'b' => -$b,
			'c' => ($v2['y'] * $b) - ($v2['x'] * $a),
		];
	}

	protected static function getBezier2CurvePoint($s, $e, $a, $t)
	{
		return [
			'x' => ($s['x'] * ((1 - $t) ** 2)) + ($a['x'] * ((1 - $t) * 2) * $t) + ($e['x'] * ($t ** 2)),
			'y' => ($s['y'] * ((1 - $t) ** 2)) + ($a['y'] * ((1 - $t) * 2) * $t) + ($e['y'] * ($t ** 2)),
		];
	}

	protected static function getBezier3CurvePoint($s, $e, $a, $b, $t)
	{
		return [
			'x' => ($s['x'] * (1 - $t) ** 3) + ($a['x'] * ((1 - $t) ** 2) * 3 * $t) + ($b['x'] * (($t ** 2) * 3 * (1 - $t))) + ($e['x'] * ($t ** 3)),
			'y' => ($s['y'] * (1 - $t) ** 3) + ($a['y'] * ((1 - $t) ** 2) * 3 * $t) + ($b['y'] * (($t ** 2) * 3 * (1 - $t))) + ($e['y'] * ($t ** 3)),
		];
	}

	protected static function getNormal($start, $end)
	{
		$vector = [
			'x' => $end['x'] - $start['x'],
			'y' => $end['y'] - $start['y'],
		];

		$len = sqrt(($vector['x'] * $vector['x']) + ($vector['y'] * $vector['y']));
		return [
			'x' => ($vector['y'] / $len),
			'y' => -($vector['x'] / $len),
		];
	}

	protected static function crossProduct($v1, $v2)
	{
		return (
			($v1[1]['x'] - $v1[0]['x']) * ($v2[1]['y'] - $v2[0]['y']) -
			($v1[1]['y'] - $v1[0]['y']) * ($v2[1]['x'] - $v2[0]['x'])
		);
	}

	protected static function getBezier2BoundingBox($bezier)
	{
		$v1 = [
			'x' => ($bezier[1]['x'] - $bezier[0]['x']),
			'y' => ($bezier[1]['y'] - $bezier[0]['y']),
		];
		$v2 = [
			'x' => ($bezier[1]['x'] - $bezier[2]['x']),
			'y' => ($bezier[1]['y'] - $bezier[2]['y']),
		];

		$base = ($v1['x'] + $v2['x']);
		if ($base != 0) {
			$t = $v1['x'] / $base;
		} else {
			$t = 0.5;
		}

		if ($t < 0) {
			$curvePointByX = $bezier[0];
		} else if ($t > 1) {
			$curvePointByX = $bezier[2];
		} else {
			$curvePointByX = self::getBezier2CurvePoint($bezier[0], $bezier[2], $bezier[1], $t);
		}

		$base = ($v1['y'] + $v2['y']);
		if ($base != 0) {
			$t = $v1['y'] / $base;
		} else {
			$t = 0.5;
		}
		if ($t < 0) {
			$curvePointByY = $bezier[0];
		} else if ($t > 1) {
			$curvePointByY = $bezier[2];
		} else {
			$curvePointByY = self::getBezier2CurvePoint($bezier[0], $bezier[2], $bezier[1], $t);
		}

		$box = self::getRectangle($bezier[0], $bezier[2]);
		$box[0]['x'] = min($curvePointByX['x'], $box[0]['x']);
		$box[0]['y'] = min($curvePointByY['y'], $box[0]['y']);

		$box[1]['x'] = max($curvePointByX['x'], $box[1]['x']);
		$box[1]['y'] = max($curvePointByY['y'], $box[1]['y']);
		return $box;
	}

	protected static function getBezier2SegmentBoundingBox($bezier, $s, $e)
	{
		$sp = self::getBezier2CurvePoint($bezier[0], $bezier[2], $bezier[1], $s);
		$ep = self::getBezier2CurvePoint($bezier[0], $bezier[2], $bezier[1], $e);


		$v1 = [
			'x' => ($bezier[1]['x'] - $bezier[0]['x']),
			'y' => ($bezier[1]['y'] - $bezier[0]['y']),
		];
		$v2 = [
			'x' => ($bezier[1]['x'] - $bezier[2]['x']),
			'y' => ($bezier[1]['y'] - $bezier[2]['y']),
		];

		$base = ($v1['x'] + $v2['x']);
		if ($base != 0) {
			$t = $v1['x'] / $base;
		} else {
			$t = 0.5;
		}
		$curvePointByX = null;
		if (($s < $t) && ($e > $t)) {
			$curvePointByX = self::getBezier2CurvePoint($bezier[0], $bezier[2], $bezier[1], $t);
		}

		$base = ($v1['y'] + $v2['y']);
		if ($base != 0) {
			$t = $v1['y'] / $base;
		} else {
			$t = 0.5;
		}
		$curvePointByY = null;
		if (($s < $t) && ($e > $t)) {
			$curvePointByY = self::getBezier2CurvePoint($bezier[0], $bezier[2], $bezier[1], $t);
		}

		$box = self::getRectangle($sp, $ep);
		if (!is_null($curvePointByX)) {
			$box[0]['x'] = min($curvePointByX['x'], $box[0]['x']);
			$box[1]['x'] = max($curvePointByX['x'], $box[1]['x']);
		}
		if (!is_null($curvePointByY)) {
			$box[0]['y'] = min($curvePointByY['y'], $box[0]['y']);
			$box[1]['y'] = max($curvePointByY['y'], $box[1]['y']);
		}
		return $box;
	}

	protected static function getBezier2SegmentTangentialLines($bezier, $s, $e)
	{
		$sp = self::getBezier2CurvePoint($bezier[0], $bezier[2], $bezier[1], $s);
		$ep = self::getBezier2CurvePoint($bezier[0], $bezier[2], $bezier[1], $e);
		$mp = self::getBezier2CurvePoint($bezier[0], $bezier[2], $bezier[1], $s + (($e - $s) / 2));


		$v1 = [
			'x' => ($bezier[1]['x'] - $bezier[0]['x']),
			'y' => ($bezier[1]['y'] - $bezier[0]['y']),
		];
		$v2 = [
			'x' => ($bezier[2]['x'] - $bezier[1]['x']),
			'y' => ($bezier[2]['y'] - $bezier[1]['y']),
		];


		$m = [
			'x' => $sp['x'] + (($ep['x'] - $sp['x']) / 2),
			'y' => $sp['y'] + (($ep['y'] - $sp['y']) / 2),
		];

		$v3 = [
			'x' => $m['x'] + ($mp['x'] - $m['x']) * 2.0,
			'y' => $m['y'] + ($mp['y'] - $m['y']) * 2.0,
		];

		return [
			$sp,
			$v3,
			$ep,
		];
	}

	protected static function getBezier2CrossLengthListByTangentialList($bezir, $tangentialList)
	{
		$count = count($tangentialList);
		$crossLengthList = [];
		for ($i = 0; $i < $count; $i++) {
			$line = [
				$tangentialList[$i],
				$tangentialList[($i + 1) % $count],
			];

			$crossInfoList = self::getBezier2CrossPointByLine($bezir, $line);
			if (!empty($crossInfoList)) {
				foreach ($crossInfoList as $c) {
					$crossLengthList[] = $c['length'];
				}
			}
		}

		if (empty($crossLengthList)) {
			return [];
		}

		if ((count($crossLengthList) % 2) != 0) {
			if (self::isInsideTriangle($tangentialList, $bezir[0])) {
				$crossLengthList[] = 0.0;
			} else {
				$crossLengthList[] = 1.0;
			}
		}

		return $crossLengthList;
	}

	protected static function isInsideTriangle($triangle, $point)
	{
		$count = count($triangle);
		for($i = 0; $i < $count; $i++) {
			$line = [
				$triangle[$i],
				$triangle[($i + 1) % $count],
			];
			$c = self::crossProduct($line, [$triangle[$i], $point]);
			if ($c > 0) {
				return false;
			}
		}
		return true;
	}

	protected static function getBezier2CrossPointByBezier2($b1, $b2)
	{
		$bezierList = [
			$b1,
			$b2,
		];

		$tangentials = [
			[
				self::getBezier2SegmentTangentialLines($b1, 0, 1),
			], [
				self::getBezier2SegmentTangentialLines($b2, 0, 1),
			],
		];

		$crossLengthList = [];
		for ($loopCounter = 0; $loopCounter < 16; $loopCounter++) {
			$isCrossedToTangential = false;
			for ($i = 0; $i < 2; $i++) {
				$crossLengthListToTangentials = [];
				foreach ($tangentials[1 - $i] as $tangentialList) {
					$len = self::getBezier2CrossLengthListByTangentialList($bezierList[$i], $tangentialList);
					$crossLengthListToTangentials = array_merge($crossLengthListToTangentials, $len);
				}
				sort($crossLengthListToTangentials);

				$count = count($crossLengthListToTangentials);
				if ($count >= 2) {
					if ($i == 0) {
						$oldCrossLengthList = $crossLengthListToTangentials;
						$crossLengthListToTangentials = [];

						$count = count($oldCrossLengthList);
						for ($ci = 0; $ci < $count; $ci += 2) {
							$diff =	 $oldCrossLengthList[$ci + 1] - $oldCrossLengthList[$ci];
							if ($diff < self::BezierCrossThreshold) {
								$crossLengthList[] = $oldCrossLengthList[$ci] + ($diff / 2);
							} else {
								$isCrossedToTangential = true;
								$crossLengthListToTangentials[] = $oldCrossLengthList[$ci];
								$crossLengthListToTangentials[] = $oldCrossLengthList[$ci + 1];
							}
						}
					}
				}

				$tangentials[$i] = [];
				$count = count($crossLengthListToTangentials);
				for ($ci = 0; $ci < $count; $ci += 2) {
					$s = $crossLengthListToTangentials[$ci];
					$e = $crossLengthListToTangentials[$ci + 1];
					$tangentials[$i][] = self::getBezier2SegmentTangentialLines($bezierList[$i], $s, $e);
				}


				if ($loopCounter > 0) {
					if (empty($tangentials[$i])) {
						break;
					}
				}

				if (false) {
					if (!empty($crossLengthListToTangentials)) {
						echo '<ul>';
						foreach ($crossLengthListToTangentials as $t) {
							echo "<li>{$t}</li>";
						}
						echo '</ul>';
					}

					echo '<hr />';
					echo '<svg width="600px" height="600px">';
					echo "<path d='M{$b1[0]['x']},{$b1[0]['y']} Q{$b1[1]['x']},{$b1[1]['y']} {$b1[2]['x']},{$b1[2]['y']}' stroke='black' fill=none />";
					echo "<path d='M{$b2[0]['x']},{$b2[0]['y']} Q{$b2[1]['x']},{$b2[1]['y']} {$b2[2]['x']},{$b2[2]['y']}' stroke='blue' fill=none />";
					if ($i != 0) {
						foreach ($tangentials[0] as $t) {
							echo "<path d='M{$t[0]['x']},{$t[0]['y']} {$t[1]['x']},{$t[1]['y']} {$t[2]['x']},{$t[2]['y']} z' stroke='#c0c0ff' fill=none />";
						}
					} else {
						foreach ($tangentials[1] as $t) {
							echo "<path d='M{$t[0]['x']},{$t[0]['y']} {$t[1]['x']},{$t[1]['y']} {$t[2]['x']},{$t[2]['y']} z' stroke='#ffc0c0' fill=none />";
						}
					}
					foreach ($crossLengthListToTangentials as $t) {
						$p = self::getBezier2CurvePoint($bezierList[$i][0], $bezierList[$i][2], $bezierList[$i][1], $t);
						echo "<circle cx='{$p['x']}' cy='{$p['y']}' r='3' fill='red'/>";
					}
					echo '</svg>';
				}
			}

			if (!$isCrossedToTangential) {
				break;
			}
		}

		$corossPoints = [];
		if (!empty($crossLengthList)) {
			foreach ($crossLengthList as $t) {
				$bezier = $bezierList[0];
				$corossPoints[] = self::getBezier2CurvePoint($bezier[0], $bezier[2], $bezier[1], $t);
			}
		}
		return $corossPoints;
	}

	protected static function getRectangle($v1, $v2)
	{
		return [
			[
				'x' => min($v1['x'], $v2['x']),
				'y' => min($v1['y'], $v2['y']),
			],
			[
				'x' => max($v1['x'], $v2['x']),
				'y' => max($v1['y'], $v2['y']),
			],
		];
	}
}

?>
