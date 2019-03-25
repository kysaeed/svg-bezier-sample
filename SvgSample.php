<?php

class SvgSample
{
	public function runDemo()
	{
		self::demoBezierCtoQ();

		self::demoBezierCross();
	}

	public static function demoBezierCtoQ()
	{
		echo '<hr />';
		echo '<h1>Cの曲線をQの連続に変換</h1>';

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




	public static function demoBezierCross()
	{
		echo '<hr /><h1>曲線と直線の交点</h1>';
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


		$crossPointList = self::getBezier2CrossPoint($curve, $line);

		echo '<ul>';
		if (!empty($crossPointList)) {
			foreach ($crossPointList as $p) {
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

		foreach ($crossPointList as $p) {
			$s .= "<circle cx='{$p['x']}' cy='{$p['y']}' r='3' fill='black' />";
		}
		$s .= '</svg>';
		echo $s;

	}

	public static function getBezier2CrossPoint($bezir, $line)
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
			$crossPointListOnCurve[] = self::getBezier2CurvePoint($b0, $b1, $cp, $t);
		}

		$crossPointListOnLine = [];
		foreach ($crossPointListOnCurve as $p) {
			if (self::isOnLine($line, $p)) {
				$crossPointListOnLine[] = $p;
			}
		}

		return $crossPointListOnLine;
	}

	protected static function isOnLine($line, $point)
	{
		$normal = self::getNormal($line[0], $line[1]);

		$v = [
			$point,
			[
				'x' => $point['x'] + $normal['x'],
				'y' => $point['y'] + $normal['y'],
			]
		];

		$crossInfo = self::getCrossPointRay($line, $v);
		if (is_null($crossInfo)) {
			return false;
		}

		if (($crossInfo['length1'] < 0) || ($crossInfo['length1'] > 1.0)) {
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
			'x' => ($s['x'] * pow(1 - $t, 2)) + ($a['x'] * ((1 - $t) * 2) * $t) + ($e['x'] * pow($t, 2)),
			'y' => ($s['y'] * pow(1 - $t, 2)) + ($a['y'] * ((1 - $t) * 2) * $t) + ($e['y'] * pow($t, 2)),
		];
	}

	protected static function getBezier3CurvePoint($s, $e, $a, $b, $t)
	{
		return [
			'x' => ($s['x'] * pow(1 - $t, 3)) + ($a['x'] * (pow(1 - $t, 2) * 3) * $t) + ($b['x'] * (pow($t, 2) * 3 * (1 - $t))) + ($e['x'] * pow($t, 3)),
			'y' => ($s['y'] * pow(1 - $t, 3)) + ($a['y'] * (pow(1 - $t, 2) * 3) * $t) + ($b['y'] * (pow($t, 2) * 3 * (1 - $t))) + ($e['y'] * pow($t, 3)),
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

	public static function crossProduct($v1, $v2)
	{
		return (
			($v1[1]['x'] - $v1[0]['x']) * ($v2[1]['y'] - $v2[0]['y']) -
			($v1[1]['y'] - $v1[0]['y']) * ($v2[1]['x'] - $v2[0]['x'])
		);
	}

	public static function getCrossPointRay($v1, $v2)
	{
		$a = self::crossProduct(
			$v1,
			[$v1[0], $v2[0]]
		);
		$b = self::crossProduct(
			$v1,
			[$v2[1], $v1[0]]
		);

		$ab = $a + $b;
		if ($ab) {
			$lengthV2  = ($a / $ab);
		} else {
			return null;
		}

		$a = self::crossProduct(
			[$v2[0], $v1[0]],
			$v2
		);

		$b = self::crossProduct(
			$v2,
			[$v2[0], $v1[1]]
		);

		$ab = ($a + $b);
		if (!$ab) {
			return null;
		}

		$lengthV1 = ($a / $ab);

		$crossed = [
			'x' => $v1[0]['x'] + (($v1[1]['x'] - $v1[0]['x']) * $lengthV1),
			'y' => $v1[0]['y'] + (($v1[1]['y'] - $v1[0]['y']) * $lengthV1),
			'length1' => $lengthV1,
			'length2' => $lengthV2,
		];

		return $crossed;
	}
}

?>
